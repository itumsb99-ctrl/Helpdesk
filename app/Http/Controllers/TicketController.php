<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SlaSetting;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\Unit;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::with(['category','requester','technician'])->latest();
        if ($user->role === 'pegawai') $query->where('user_id',$user->id);
        if ($user->role === 'teknisi') $query->where('technician_id',$user->id);
        if ($request->filled('q')) $query->where(fn($q)=>$q->where('ticket_number','like','%'.$request->q.'%')->orWhere('title','like','%'.$request->q.'%'));
        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('priority')) $query->where('priority',$request->priority);
        return view('tickets.index',['tickets'=>$query->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('tickets.create', ['categories' => Category::where('is_active', true)->orderBy('name')->get(), 'units' => Unit::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'location' => ['required', 'string', 'max:180'],
            'unit_id' => ['required', 'exists:units,id'],
        ]);

        $selectedUnit = Unit::where('is_active', true)->find($data['unit_id']);
        if (!$selectedUnit) {
            return back()->withInput()->withErrors(['unit_id' => 'Unit tidak tersedia atau sudah dinonaktifkan.']);
        }
        $data['unit'] = $selectedUnit->name;

        $sla = SlaSetting::where('priority', $data['priority'])
            ->where('is_active', true)
            ->first();

        if (!$sla) {
            return back()
                ->withInput()
                ->withErrors([
                    'priority' => 'Pengaturan SLA untuk prioritas ini belum aktif. Hubungi Admin IT.'
                ]);
        }

        $ticket = DB::transaction(function () use ($data, $request, $sla) {
            $now = now();

            $ticket = Ticket::create($data + [
                'ticket_number' => 'HD-' . $now->format('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'user_id' => $request->user()->id,
                'status' => 'open',
                'response_due_at' => $now->copy()->addMinutes($sla->response_minutes),
                'resolution_due_at' => $now->copy()->addMinutes($sla->resolution_minutes),
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'event' => 'created',
                'new_value' => 'Tiket dibuat',
            ]);

            return $ticket;
        });

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Tiket berhasil dibuat: ' . $ticket->ticket_number);
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->load(['category','requester','technician','comments.user','histories']);
        $technicians = auth()->user()->role === 'admin' ? User::where('role','teknisi')->where('is_active',true)->orderBy('name')->get() : collect();
        return view('tickets.show',compact('ticket','technicians'));
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $data = $request->validate(['technician_id'=>['required','exists:users,id']]);
        $tech = User::where('role','teknisi')->where('is_active',true)->findOrFail($data['technician_id']);
        $old = $ticket->technician_id;
        $ticket->update(['technician_id'=>$tech->id,'status'=>'assigned']);
        TicketHistory::create(['ticket_id'=>$ticket->id,'user_id'=>$request->user()->id,'event'=>'assigned','old_value'=>(string)$old,'new_value'=>$tech->name]);
        return back()->with('success','Teknisi berhasil ditugaskan.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:assigned,in_progress,pending,resolved,closed'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        if (
            $request->user()->role === 'teknisi' &&
            $ticket->technician_id !== $request->user()->id
        ) {
            abort(403);
        }

        $old = $ticket->status;
        $new = $data['status'];

        $updates = ['status' => $new];

        if ($new === 'resolved' && !$ticket->resolved_at) {
            $updates['resolved_at'] = now();
        }

        if ($new === 'closed' && !$ticket->closed_at) {
            $updates['closed_at'] = now();
        }

        // First response hanya dihitung ketika teknisi merespons
        if (
            $request->user()->role === 'teknisi' &&
            !$ticket->first_response_at
        ) {
            $updates['first_response_at'] = now();
        }

        DB::transaction(function () use ($ticket, $request, $updates, $old, $new, $data) {
            $ticket->update($updates);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'event' => 'status',
                'old_value' => $old,
                'new_value' => $new,
            ]);

            if (!empty($data['note'])) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $request->user()->id,
                    'body' => $data['note'],
                ]);
            }
        });

        return back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function comment(Request $request, Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($request, $ticket, $data) {
            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'body' => $data['body'],
            ]);

            if (
                $request->user()->role === 'teknisi' &&
                !$ticket->first_response_at
            ) {
                $ticket->update([
                    'first_response_at' => now(),
                ]);
            }

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'event' => 'comment',
                'new_value' => 'Komentar ditambahkan',
            ]);
        });

        return back()->with('success', 'Catatan berhasil ditambahkan.');
    }

    private function authorizeTicket(Ticket $ticket): void
    {
        $user = auth()->user();
        if ($user->role === 'pegawai' && $ticket->user_id !== $user->id) abort(403);
        if ($user->role === 'teknisi' && $ticket->technician_id !== $user->id && $ticket->user_id !== $user->id) abort(403);
    }
}
