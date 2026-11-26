<?php

namespace App\\Notifications;

use Illuminate\\Bus\\Queueable;
use Illuminate\\Notifications\\Notification;

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
        return ['database'];
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
}
