<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
@page { margin: 22px 24px; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #25404a; }
h1 { font-size: 18px; margin: 0 0 5px; color: #087f86; }
.logo { width: 58px; height: 58px; object-fit: contain; }
.header { width: 100%; border-bottom: 2px solid #9dc42c; margin-bottom: 10px; padding-bottom: 8px; }
.muted { color: #667085; }
.summary { width: 100%; margin: 14px 0; border-collapse: collapse; }
.summary td { border: 1px solid #d9e0e8; padding: 7px; width: 20%; }
table.data { width: 100%; border-collapse: collapse; }
.data th, .data td { border: 1px solid #cfd7e2; padding: 5px; vertical-align: top; }
.data th { background: #e8f4f1; color:#075b63; text-align: left; }
</style>
</head>
<body>
<table class="header"><tr><td style="width:68px"><img class="logo" src="{{ public_path('images/unimedika-logo.jpg') }}"></td><td><h1>Rumah Sakit UniMedika Setu Bekasi</h1><strong>Laporan Tiket IT Helpdesk</strong><div class="muted">Sistem Layanan Teknologi Informasi · Dicetak {{ now()->format('d/m/Y H:i') }}</div></td></tr></table>
<p>
    Periode: {{ $filters['start_date'] ?? 'Semua tanggal' }} s.d. {{ $filters['end_date'] ?? 'Sekarang' }} |
    Unit: {{ $filters['unit'] ?? 'Semua unit' }} |
    Status: {{ isset($filters['status']) ? ucfirst(str_replace('_', ' ', $filters['status'])) : 'Semua status' }} |
    Prioritas: {{ isset($filters['priority']) ? strtoupper($filters['priority']) : 'Semua prioritas' }}
</p>
<table class="summary"><tr>
    <td><strong>Total Tiket</strong><br>{{ $summary['total'] }}</td>
    <td><strong>Baru / Ditugaskan</strong><br>{{ $summary['open'] }}</td>
    <td><strong>Diproses</strong><br>{{ $summary['in_progress'] }}</td>
    <td><strong>Selesai / Ditutup</strong><br>{{ $summary['resolved'] }}</td>
    <td><strong>SLA Respons</strong><br>{{ $summary['response_rate'] === null ? '-' : $summary['response_rate'].'%' }}</td>
</tr></table>
<p><strong>Pencapaian SLA Penyelesaian:</strong> {{ $summary['resolution_rate'] === null ? '-' : $summary['resolution_rate'].'%' }} ({{ $summary['resolution_met'] }} dari {{ $summary['resolution_eligible'] }} tiket selesai).</p>
<table class="data">
<thead><tr><th>No.</th><th>Tanggal</th><th>Judul</th><th>Pemohon</th><th>Unit</th><th>Kategori</th><th>Prioritas</th><th>Status</th><th>Teknisi</th><th>SLA Respons</th><th>SLA Penyelesaian</th></tr></thead>
<tbody>
@forelse($tickets as $ticket)
@php
    $responseOk = $ticket->first_response_at && $ticket->response_due_at && $ticket->first_response_at->lessThanOrEqualTo($ticket->response_due_at);
    $completedAt = $ticket->resolved_at ?? $ticket->closed_at;
    $resolutionOk = $completedAt && $ticket->resolution_due_at && $completedAt->lessThanOrEqualTo($ticket->resolution_due_at);
@endphp
<tr>
    <td>{{ $ticket->ticket_number }}</td><td>{{ $ticket->created_at?->format('d/m/Y H:i') }}</td><td>{{ $ticket->title }}</td><td>{{ $ticket->requester?->name ?? '-' }}</td><td>{{ $ticket->unit }}</td><td>{{ $ticket->category?->name ?? '-' }}</td><td>{{ strtoupper($ticket->priority) }}</td><td>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td><td>{{ $ticket->technician?->name ?? '-' }}</td>
    <td>{{ !$ticket->response_due_at ? '-' : (!$ticket->first_response_at ? 'Belum respons' : ($responseOk ? 'Tercapai' : 'Terlambat')) }}</td>
    <td>{{ !$ticket->resolution_due_at ? '-' : (!$completedAt ? 'Belum selesai' : ($resolutionOk ? 'Tercapai' : 'Terlambat')) }}</td>
</tr>
@empty<tr><td colspan="11">Tidak ada tiket untuk filter yang dipilih.</td></tr>@endforelse
</tbody></table>
</body></html>
