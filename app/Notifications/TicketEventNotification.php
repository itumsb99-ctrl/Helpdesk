<?php

namespace App\\Notifications;

use App\\Models\\Ticket;
use Illuminate\\Bus\\Queueable;
use Illuminate\\Notifications\\Messages\\MailMessage;
use Illuminate\\Notifications\\Notification;

class TicketEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $event
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticketUrl = route('tickets.show', $this->ticket);

        $mail = (new MailMessage)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->subject($this->subject())
            ->line($this->intro())
            ->line('Nomor tiket: ' . $this->ticket->ticket_number)
            ->line('Judul: ' . $this->ticket->title)
            ->line('Prioritas: ' . ucfirst($this->ticket->priority))
            ->line('Status: ' . ucfirst(str_replace('_', ' ', $this->ticket->status)));

        if ($this->ticket->unit) {
            $mail->line('Unit: ' . $this->ticket->unit);
        }

        return $mail
            ->action('Lihat Detail Tiket', $ticketUrl)
            ->line('Email ini dikirim otomatis oleh IT Helpdesk Rumah Sakit UniMedika.');
    }

    private function subject(): string
    {
        return match ($this->event) {
            'ticket_created' => 'Tiket IT Helpdesk Baru - ' . $this->ticket->ticket_number,
            'ticket_assigned' => 'Penugasan Tiket IT Helpdesk - ' . $this->ticket->ticket_number,
            default => 'Pembaruan Tiket IT Helpdesk - ' . $this->ticket->ticket_number,
        };
    }

    private function intro(): string
    {
        return match ($this->event) {
            'ticket_created' => 'Tiket gangguan baru telah berhasil dibuat.',
            'ticket_assigned' => 'Anda telah ditugaskan untuk menangani tiket berikut.',
            default => 'Terdapat pembaruan pada tiket IT Helpdesk.',
        };
    }
}
