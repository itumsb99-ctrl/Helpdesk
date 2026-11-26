<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\TicketHistory;
use Illuminate\Console\Command;

class CheckTicketSla extends Command
{
    protected $signature = 'helpdesk:check-sla';
    protected $description = 'Tandai tiket yang melewati batas SLA dan catat eskalasi satu kali';

    public function handle(): int
    {
        $tickets = Ticket::whereNull('escalated_at')
            ->whereNotIn('status',['resolved','closed'])
            ->where('resolution_due_at','<',now())->get();

        foreach ($tickets as $ticket) {
            $ticket->update(['escalated_at'=>now()]);
            TicketHistory::create([
                'ticket_id'=>$ticket->id,
                'user_id'=>null,
                'event'=>'sla_escalated',
                'old_value'=>null,
                'new_value'=>'Batas penyelesaian SLA terlewati',
            ]);
        }
        $this->info($tickets->count().' tiket dieskalasikan.');
        return self::SUCCESS;
    }
}
