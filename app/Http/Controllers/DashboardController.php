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
        if ($user->role === 'pegawai') $query->where('user_id', $user->id);
        if ($user->role === 'teknisi') $query->where('technician_id', $user->id);

        $tickets = (clone $query)->with(['category','requester','technician'])->latest()->limit(8)->get();
        $counts = [
            'total'=>(clone $query)->count(),
            'open'=>(clone $query)->whereIn('status',['open','assigned'])->count(),
            'progress'=>(clone $query)->where('status','in_progress')->count(),
            'resolved'=>(clone $query)->whereIn('status',['resolved','closed'])->count(),
            'overdue'=>(clone $query)->whereNotIn('status',['resolved','closed'])->where('resolution_due_at','<',now())->count(),
        ];

        $trend = (clone $query)->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at','>=',now()->subDays(6)->startOfDay())
            ->groupBy('day')->orderBy('day')->get();

        $statusLabels = ['open'=>'Baru','assigned'=>'Ditugaskan','in_progress'=>'Diproses','pending'=>'Menunggu','resolved'=>'Selesai','closed'=>'Ditutup'];
        $statusData = (clone $query)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->get();

        return view('dashboard', compact('tickets','counts','trend','statusLabels','statusData'));
    }
}
