<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TicketReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $tickets)
    {
    }

    public function collection(): Collection
    {
        return $this->tickets;
    }

    public function headings(): array
    {
        return [
            ['Rumah Sakit UniMedika Setu Bekasi - Laporan Tiket IT Helpdesk'],
            ['No. Tiket', 'Tanggal Dibuat', 'Judul', 'Pemohon', 'Unit', 'Lokasi',
            'Kategori', 'Prioritas', 'Status', 'Teknisi', 'Batas Respons SLA',
            'Respons Pertama', 'Batas Penyelesaian SLA', 'Diselesaikan Pada',
            'Status SLA Respons', 'Status SLA Penyelesaian'],
        ];
    }

    public function map($ticket): array
    {
        $responseStatus = !$ticket->response_due_at
            ? 'Tidak tersedia'
            : (!$ticket->first_response_at
                ? (now()->greaterThan($ticket->response_due_at) ? 'Terlambat / belum direspons' : 'Menunggu respons')
                : ($ticket->first_response_at->lessThanOrEqualTo($ticket->response_due_at) ? 'Tercapai' : 'Terlambat'));

        $completedAt = $ticket->resolved_at ?? $ticket->closed_at;
        $resolutionStatus = !$ticket->resolution_due_at
            ? 'Tidak tersedia'
            : (!$completedAt
                ? (now()->greaterThan($ticket->resolution_due_at) ? 'Terlambat / belum selesai' : 'Dalam proses')
                : ($completedAt->lessThanOrEqualTo($ticket->resolution_due_at) ? 'Tercapai' : 'Terlambat'));

        return [
            $ticket->ticket_number,
            $ticket->created_at?->format('d-m-Y H:i'),
            $ticket->title,
            $ticket->requester?->name ?? '-',
            $ticket->unit,
            $ticket->location,
            $ticket->category?->name ?? '-',
            strtoupper($ticket->priority),
            ucfirst(str_replace('_', ' ', $ticket->status)),
            $ticket->technician?->name ?? 'Belum ditugaskan',
            $ticket->response_due_at?->format('d-m-Y H:i') ?? '-',
            $ticket->first_response_at?->format('d-m-Y H:i') ?? '-',
            $ticket->resolution_due_at?->format('d-m-Y H:i') ?? '-',
            $completedAt?->format('d-m-Y H:i') ?? '-',
            $responseStatus,
            $resolutionStatus,
        ];
    }
}
