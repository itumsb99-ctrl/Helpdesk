<?php

namespace App\Http\Controllers;

use App\Exports\TicketReportExport;
use App\Models\Ticket;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class TicketReportController extends Controller
{
    private const STATUSES = ['open', 'assigned', 'in_progress', 'pending', 'resolved', 'closed'];
    private const PRIORITIES = ['critical', 'high', 'medium', 'low'];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $query = $this->ticketQuery($filters);
        $allTickets = (clone $query)->get();

        return view('reports.tickets.index', [
            'tickets' => $query->paginate(20)->withQueryString(),
            'summary' => $this->summarize($allTickets),
            'filters' => $filters,
            'units' => Ticket::query()->whereNotNull('unit')->where('unit', '<>', '')
                ->distinct()->orderBy('unit')->pluck('unit'),
            'technicians' => User::query()->where('role', 'teknisi')->orderBy('name')->get(),
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
        ]);
    }

    public function excel(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $tickets = $this->ticketQuery($filters)->get();

        return Excel::download(
            new TicketReportExport($tickets),
            'laporan-tiket-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function pdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $tickets = $this->ticketQuery($filters)->get();
        $summary = $this->summarize($tickets);

        return Pdf::loadView('reports.tickets.pdf', compact('tickets', 'summary', 'filters'))
            ->setPaper('a4', 'landscape')
            ->download('laporan-tiket-' . now()->format('Ymd-His') . '.pdf');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'unit' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:' . implode(',', self::STATUSES)],
            'priority' => ['nullable', 'in:' . implode(',', self::PRIORITIES)],
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }

    private function ticketQuery(array $filters)
    {
        return Ticket::query()
            ->with(['category', 'requester', 'technician'])
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->where('created_at', '>=', $date . ' 00:00:00'))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->where('created_at', '<=', $date . ' 23:59:59'))
            ->when($filters['unit'] ?? null, fn ($query, $unit) => $query->where('unit', $unit))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
            ->when($filters['technician_id'] ?? null, fn ($query, $id) => $query->where('technician_id', $id))
            ->latest();
    }

    private function summarize(Collection $tickets): array
    {
        $responseEligible = $tickets->filter(fn ($ticket) => $ticket->response_due_at && $ticket->first_response_at);
        $resolutionEligible = $tickets->filter(fn ($ticket) => $ticket->resolution_due_at && ($ticket->resolved_at || $ticket->closed_at));

        $responseMet = $responseEligible->filter(fn ($ticket) => $ticket->first_response_at->lessThanOrEqualTo($ticket->response_due_at))->count();
        $resolutionMet = $resolutionEligible->filter(function ($ticket) {
            $completedAt = $ticket->resolved_at ?? $ticket->closed_at;

            return $completedAt->lessThanOrEqualTo($ticket->resolution_due_at);
        })->count();

        return [
            'total' => $tickets->count(),
            'open' => $tickets->whereIn('status', ['open', 'assigned'])->count(),
            'in_progress' => $tickets->whereIn('status', ['in_progress', 'pending'])->count(),
            'resolved' => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
            'response_eligible' => $responseEligible->count(),
            'response_met' => $responseMet,
            'response_rate' => $responseEligible->count() ? round($responseMet / $responseEligible->count() * 100, 1) : null,
            'resolution_eligible' => $resolutionEligible->count(),
            'resolution_met' => $resolutionMet,
            'resolution_rate' => $resolutionEligible->count() ? round($resolutionMet / $resolutionEligible->count() * 100, 1) : null,
        ];
    }
}
