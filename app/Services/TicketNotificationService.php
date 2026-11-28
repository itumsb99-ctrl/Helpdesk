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

    private function send(iterable $recipients, Ticket $ticket, string $event): void
    {
        try {
            Notification::send($recipients, new TicketEventNotification($ticket, $event));
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim notifikasi email tiket.', [
                'ticket_id' => $ticket->id,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
