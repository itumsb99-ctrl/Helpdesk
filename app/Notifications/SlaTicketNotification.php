<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaTicketNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public int $ticketId,
        public string $ticketNumber,
        public string $priority,
        public string $event
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'ticket_id' => $this->ticketId,
            'ticket_number' => $this->ticketNumber,
            'priority' => $this->priority,
            'event' => $this->event,
            'url' => route('tickets.show', $this->ticketId),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->subject($this->title . ' - ' . $this->ticketNumber)
            ->line($this->message)
            ->line('Nomor tiket: ' . $this->ticketNumber)
            ->line('Prioritas: ' . ucfirst($this->priority))
            ->action('Lihat Detail Tiket', route('tickets.show', $this->ticketId))
            ->line('Email ini dikirim otomatis oleh IT Helpdesk Rumah Sakit UniMedika.');
    }
}
