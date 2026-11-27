<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $query = Ticket::query();

        if ($user->role === 'pegawai') {
            $query->where('user_id', $user->id);
        }

        if ($user->role === 'teknisi') {
            $query->where('technician_id', $user->id);
        }

        $now = now();
        $warningWindow = $now->copy()->addMinutes(30);

        $activeTickets = fn () => (clone $query)
            ->whereNotIn('status', ['resolved', 'closed']);

        $responseWarningQuery = fn () => $activeTickets()
            ->whereNull('first_response_at')
            ->whereNotNull('response_due_at')
            ->where('response_due_at', '>', $now)
            ->where('response_due_at', '<=', $warningWindow);

        $resolutionWarningQuery = fn () => $activeTickets()
            ->whereNotNull('resolution_due_at')
            ->where('resolution_due_at', '>', $now)
            ->where('resolution_due_at', '<=', $warningWindow);

        $responseOverdueQuery = fn () => $activeTickets()
            ->whereNull('first_response_at')
            ->whereNotNull('response_due_at')
            ->where('response_due_at', '<=', $now);

        $resolutionOverdueQuery = fn () => $activeTickets()
            ->whereNotNull('resolution_due_at')
            ->where('resolution_due_at', '<=', $now);

        $overdueQuery = fn () => $activeTickets()->where(function ($q) use ($now) {
            $q->where(function ($response) use ($now) {
                $response->whereNull('first_response_at')
                    ->whereNotNull('response_due_at')
                    ->where('response_due_at', '<=', $now);
            })->orWhere(function ($resolution) use ($now) {
                $resolution->whereNotNull('resolution_due_at')
                    ->where('resolution_due_at', '<=', $now);
            });
        });

        $tickets = (clone $query)
            ->with(['category', 'requester', 'technician'])
            ->latest()
            ->limit(8)
            ->get();

        $counts = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->whereIn('status', ['open', 'assigned'])->count(),
            'progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->whereIn('status', ['resolved', 'closed'])->count(),
            'response_warning' => $responseWarningQuery()->count(),
            'resolution_warning' => $resolutionWarningQuery()->count(),
            'response_overdue' => $responseOverdueQuery()->count(),
            'resolution_overdue' => $resolutionOverdueQuery()->count(),
            'overdue' => $overdueQuery()->count(),
        ];

        $slaMonitorTickets = (clone $activeTickets())
            ->where(function ($q) use ($now, $warningWindow) {
                $q->where(function ($response) use ($now, $warningWindow) {
                    $response->whereNull('first_response_at')
                        ->whereNotNull('response_due_at')
                        ->where('response_due_at', '<=', $warningWindow);
                })->orWhere(function ($resolution) use ($now, $warningWindow) {
                    $resolution->whereNotNull('resolution_due_at')
                        ->where('resolution_due_at', '<=', $warningWindow);
                });
            })
            ->with(['requester', 'technician'])
            ->orderByRaw('CASE WHEN first_response_at IS NULL AND response_due_at IS NOT NULL AND response_due_at <= ? THEN response_due_at ELSE COALESCE(resolution_due_at, response_due_at) END ASC', [$warningWindow])
            ->limit(20)
            ->get()
            ->map(function (Ticket $ticket) use ($now) {
                $monitoring = [];

                if (!$ticket->first_response_at && $ticket->response_due_at) {
                    $overdue = $ticket->response_due_at->lessThanOrEqualTo($now);

                    $monitoring[] = [
                        'label' => $overdue ? 'Respons terlambat' : 'Batas respons mendekat',
                        'state' => $overdue ? 'overdue' : 'warning',
                        'due_at' => $ticket->response_due_at,
                        'duration' => $ticket->response_due_at->diffForHumans($now, true),
                    ];
                }

                if ($ticket->resolution_due_at) {
                    $overdue = $ticket->resolution_due_at->lessThanOrEqualTo($now);

                    $monitoring[] = [
                        'label' => $overdue ? 'Penyelesaian terlambat' : 'Batas penyelesaian mendekat',
                        'state' => $overdue ? 'overdue' : 'warning',
                        'due_at' => $ticket->resolution_due_at,
                        'duration' => $ticket->resolution_due_at->diffForHumans($now, true),
                    ];
                }

                $ticket->setAttribute('sla_monitoring', $monitoring);

                return $ticket;
            });

        $trend = (clone $query)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $statusLabels = [
            'open' => 'Baru',
            'assigned' => 'Ditugaskan',
            'in_progress' => 'Diproses',
            'pending' => 'Menunggu',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup',
        ];

        $statusData = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        return view('dashboard', compact(
            'tickets',
            'counts',
            'trend',
            'statusLabels',
            'statusData',
            'slaMonitorTickets'
        ));
    }
}
