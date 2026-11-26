<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    private function checkAccess(Ticket $ticket): void
    {
        $user = auth()->user();

        $allowed = $user->role === 'admin'
            || (int) $ticket->user_id === (int) $user->id
            || (int) $ticket->technician_id === (int) $user->id;

        abort_unless($allowed, 403, 'Akses tiket ditolak.');
    }

    public function store(Request $request, Ticket $ticket)
    {
        $this->checkAccess($ticket);

        $validated = $request->validate([
            'attachments' => ['required', 'array', 'max:5'],
            'attachments.*' => [
                'required',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,docx',
            ],
        ]);

        foreach ($validated['attachments'] as $file) {
            $path = $file->store('ticket-attachments', 'local');

            $ticket->attachments()->create([
                'uploaded_by' => auth()->id(),
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

    public function download(TicketAttachment $attachment)
    {
        $this->checkAccess($attachment->ticket);

        abort_unless(
            Storage::disk('local')->exists($attachment->file_path),
            404
        );

        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    public function destroy(TicketAttachment $attachment)
    {
        $this->checkAccess($attachment->ticket);

        $user = auth()->user();

        abort_unless(
            $user->role === 'admin'
            || (int) $attachment->uploaded_by === (int) $user->id,
            403,
            'Anda tidak berhak menghapus lampiran.'
        );

        Storage::disk('local')->delete($attachment->file_path);

        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
