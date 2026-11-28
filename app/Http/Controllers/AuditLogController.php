<?php

namespace App\Http\Controllers;

use App\Models\TicketHistory;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketHistory::with([
            'ticket:id,ticket_number,title',
            'user:id,name,employee_id,role',
        ])->latest();

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->whereHas('ticket', function ($ticket) use ($search) {
                $ticket->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->string('event')->toString());
        }

        if ($request->filled('actor')) {
            $actor = $request->string('actor')->toString();
            $query->whereHas('user', function ($user) use ($actor) {
                $user->where('name', 'like', "%{$actor}%")
                    ->orWhere('employee_id', 'like', "%{$actor}%");
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        return view('audit-logs.index', [
            'histories' => $query->paginate(20)->withQueryString(),
        ]);
    }
}
