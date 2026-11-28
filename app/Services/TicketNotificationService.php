<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class TicketNotificationService
{
    public function ticketCreated(Ticket $ticket): void
    {
        $recipients = User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($ticket) {
                $query->whereKey($ticket->user_id)
                    ->orWhere('role', 'admin');
            })
            ->get();

        $this->send($recipients, $ticket, 'ticket_created');
    }

    public function ticketAssigned(Ticket $ticket, User $technician): void
    {
        if (!$technician->is_active || !$technician->email) {
            return;
        }

        $this->send(collect([$technician]), $ticket, 'ticket_assigned');
    }

    public function ticketStatusChanged(Ticket $ticket, User $actor, string $oldStatus, string $newStatus): void
    {
        $recipients = $this->stakeholders($ticket, $actor);
        $this->send($recipients, $ticket, 'status_changed', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    public function ticketCommentAdded(Ticket $ticket, User $actor, string $comment): void
    {
        $recipients = $this->stakeholders($ticket, $actor);
        $this->send($recipients, $ticket, 'comment_added', [
            'comment' => $comment,
        ]);
    }

    private function stakeholders(Ticket $ticket, User $actor)
    {
        return User::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where('id', '!=', $actor->id)
            ->where(function ($query) use ($ticket) {
                $query->whereKey($ticket->user_id)
                    ->orWhere('id', $ticket->technician_id)
                    ->orWhere('role', 'admin');
            })
            ->get();
    }

    private function send(iterable $recipients, Ticket $ticket, string $event, array $details = []): void
    {
        try {
            Notification::send($recipients, new TicketEventNotification($ticket, $event, $details));
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim notifikasi email tiket.', [
                'ticket_id' => $ticket->id,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
