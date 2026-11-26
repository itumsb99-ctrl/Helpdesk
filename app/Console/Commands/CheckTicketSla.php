<?php

namespace App\\Console\\Commands;

use App\\Models\\Ticket;
use App\\Models\\TicketHistory;
use App\\Models\\User;
use App\\Notifications\\SlaTicketNotification;
use Illuminate\\Console\\Command;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Notification;

class CheckTicketSla extends Command
{
    protected $signature = 'helpdesk:check-sla {--warning-minutes=30 : Jumlah menit sebelum batas SLA untuk mengirim peringatan}';
    protected $description = 'Kirim peringatan SLA, eskalasi tiket terlambat, dan beri notifikasi kepada teknisi serta Admin IT';

    public function handle(): int
    {
        $now = now();
        $warningWindow = $now->copy()->addMinutes(max(1, (int) $this->option('warning-minutes')));

        $tickets = Ticket::query()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where(function ($query) use ($warningWindow) {
                $query->where(function ($q) use ($warningWindow) {
                    $q->whereNull('first_response_at')
                        ->whereNull('response_warning_sent_at')
                        ->whereNotNull('response_due_at')
                        ->where('response_due_at', '<=', $warningWindow);
                })->orWhere(function ($q) use ($warningWindow) {
                    $q->whereNull('resolution_warning_sent_at')
                        ->whereNotNull('resolution_due_at')
                        ->where('resolution_due_at', '<=', $warningWindow);
                })->orWhere(function ($q) use ($now) {
                    $q->whereNull('escalated_at')
                        ->where(function ($overdue) use ($now) {
                            $overdue->where(function ($response) use ($now) {
                                $response->whereNull('first_response_at')
                                    ->whereNotNull('response_due_at')
                                    ->where('response_due_at', '<=', $now);
                            })->orWhere(function ($resolution) use ($now) {
                                $resolution->whereNotNull('resolution_due_at')
                                    ->where('resolution_due_at', '<=', $now);
                            });
                        });
                });
            })
            ->pluck('id');

        $warnings = 0;
        $escalations = 0;

        foreach ($tickets as $ticketId) {
            DB::transaction(function () use ($ticketId, $now, $warningWindow, &$warnings, &$escalations) {
                $ticket = Ticket::whereKey($ticketId)->lockForUpdate()->first();

                if (!$ticket || in_array($ticket->status, ['resolved', 'closed'], true)) {
                    return;
                }

                if (
                    !$ticket->first_response_at &&
                    !$ticket->response_warning_sent_at &&
                    $ticket->response_due_at &&
                    $ticket->response_due_at->greaterThan($now) &&
                    $ticket->response_due_at->lessThanOrEqualTo($warningWindow)
                ) {
                    $this->notifyAudience(
                        $ticket,
                        'Peringatan SLA respons',
                        'Tiket ' . $ticket->ticket_number . ' mendekati batas waktu respons teknisi.',
                        'response_warning'
                    );
                    $ticket->response_warning_sent_at = $now;
                    $warnings++;
                }

                if (
                    !$ticket->resolution_warning_sent_at &&
                    $ticket->resolution_due_at &&
                    $ticket->resolution_due_at->greaterThan($now) &&
                    $ticket->resolution_due_at->lessThanOrEqualTo($warningWindow)
                ) {
                    $this->notifyAudience(
                        $ticket,
                        'Peringatan SLA penyelesaian',
                        'Tiket ' . $ticket->ticket_number . ' mendekati batas waktu penyelesaian.',
                        'resolution_warning'
                    );
                    $ticket->resolution_warning_sent_at = $now;
                    $warnings++;
                }

                $responseOverdue = !$ticket->first_response_at
                    && $ticket->response_due_at
                    && $ticket->response_due_at->lessThanOrEqualTo($now);

                $resolutionOverdue = $ticket->resolution_due_at
                    && $ticket->resolution_due_at->lessThanOrEqualTo($now);

                if (!$ticket->escalated_at && ($responseOverdue || $resolutionOverdue)) {
                    $reason = $responseOverdue && $resolutionOverdue
                        ? 'Batas respons dan penyelesaian SLA terlewati'
                        : ($responseOverdue
                            ? 'Batas waktu respons SLA terlewati'
                            : 'Batas waktu penyelesaian SLA terlewati');

                    $ticket->escalated_at = $now;
                    TicketHistory::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => null,
                        'event' => 'sla_escalated',
                        'old_value' => null,
                        'new_value' => $reason,
                    ]);

                    $this->notifyAudience(
                        $ticket,
                        'Eskalasi tiket SLA',
                        'Tiket ' . $ticket->ticket_number . ' telah melewati SLA. ' . $reason . '. Segera tindak lanjuti.',
                        'escalation'
                    );
                    $escalations++;
                }

                if ($ticket->isDirty()) {
                    $ticket->save();
                }
            });
        }

        $this->info($warnings . ' peringatan SLA dikirim; ' . $escalations . ' tiket dieskalasikan.');
        return self::SUCCESS;
    }

    private function notifyAudience(Ticket $ticket, string $title, string $message, string $event): void
    {
        $recipients = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->get();

        if ($ticket->technician_id) {
            $technician = User::query()
                ->where('role', 'teknisi')
                ->where('is_active', true)
                ->whereKey($ticket->technician_id)
                ->first();

            if ($technician) {
                $recipients->push($technician);
            }
        }

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients->unique('id'), new SlaTicketNotification(
                $title,
                $message,
                (int) $ticket->id,
                $ticket->ticket_number,
                $ticket->priority,
                $event
            ));
        }
    }
}
