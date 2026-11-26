<?php

namespace App\\Http\\Controllers;

use App\\Models\\Ticket;
use Illuminate\\Support\\Facades\\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $query = Ticket::query();
        if ($user->role === 'pegawai') $query->where('user_id', $user->id);
        if ($user->role === 'teknisi') $query->where('technician_id', $user->id);

        $tickets = (clone $query)->with(['category','requester','technician'])->latest()->limit(8)->get();
        $overdueQuery = (clone $query)->whereNotIn('status', ['resolved','closed'])
            ->where(function ($q) {
                $q->where(function ($r) {
                    $r->whereNull('first_response_at')->whereNotNull('response_due_at')->where('response_due_at','<',now());
                })->orWhere(function ($s) {
                    $s->whereNotNull('resolution_due_at')->where('resolution_due_at','<',now());
                });
            });

        $counts = [
            'total'=>(clone $query)->count(),
            'open'=>(clone $query)->whereIn('status',['open','assigned'])->count(),
            'progress'=>(clone $query)->where('status','in_progress')->count(),
            'resolved'=>(clone $query)->whereIn('status',['resolved','closed'])->count(),
            'overdue'=>(clone $overdueQuery)->count(),
        ];
        $overdueTickets = (clone $overdueQuery)->with(['requester','technician'])->orderBy('resolution_due_at')->limit(10)->get();
        $trend = (clone $query)->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at','>=',now()->subDays(6)->startOfDay())->groupBy('day')->orderBy('day')->get();
        $statusLabels = ['open'=>'Baru','assigned'=>'Ditugaskan','in_progress'=>'Diproses','pending'=>'Menunggu','resolved'=>'Selesai','closed'=>'Ditutup'];
        $statusData = (clone $query)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->get();

        return view('dashboard', compact('tickets','counts','trend','statusLabels','statusData','overdueTickets'));
    }
}
