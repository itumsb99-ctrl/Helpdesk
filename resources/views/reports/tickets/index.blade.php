@extends('layouts.app')
@section('title', 'Laporan Tiket')
@section('heading', 'Laporan Tiket')
@section('content')
<div class="card content-card mb-4"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h5 class="fw-bold mb-1">Filter Laporan</h5><div class="text-secondary small">Filter tanggal berdasarkan waktu tiket dibuat.</div></div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.tickets.pdf', request()->query()) }}" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</a>
            <a href="{{ route('reports.tickets.excel', request()->query()) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
        </div>
    </div>
    <form method="GET" action="{{ route('reports.tickets.index') }}" class="row g-3">
        <div class="col-md-3"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}"></div>
        <div class="col-md-3"><label class="form-label">Tanggal Akhir</label><input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}"></div>
        <div class="col-md-3"><label class="form-label">Unit</label><select name="unit" class="form-select"><option value="">Semua unit</option>@foreach($units as $unit)<option value="{{ $unit }}" @selected(($filters['unit'] ?? '') === $unit)>{{ $unit }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">Semua status</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Prioritas</label><select name="priority" class="form-select"><option value="">Semua prioritas</option>@foreach($priorities as $priority)<option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Teknisi</label><select name="technician_id" class="form-select"><option value="">Semua teknisi</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string)($filters['technician_id'] ?? '') === (string)$technician->id)>{{ $technician->name }}</option>@endforeach</select></div>
        <div class="col-md-6 d-flex align-items-end gap-2"><button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button><a href="{{ route('reports.tickets.index') }}" class="btn btn-light border">Reset</a></div>
    </form>
</div></div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="card stat h-100"><div class="card-body"><div class="small text-secondary">Total Tiket</div><div class="fs-3 fw-bold">{{ number_format($summary['total']) }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card stat h-100"><div class="card-body"><div class="small text-secondary">Tiket Baru / Ditugaskan</div><div class="fs-3 fw-bold">{{ number_format($summary['open']) }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card stat h-100"><div class="card-body"><div class="small text-secondary">Sedang Diproses</div><div class="fs-3 fw-bold">{{ number_format($summary['in_progress']) }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card stat h-100"><div class="card-body"><div class="small text-secondary">Selesai / Ditutup</div><div class="fs-3 fw-bold">{{ number_format($summary['resolved']) }}</div></div></div></div>
    <div class="col-md-6"><div class="card stat h-100"><div class="card-body"><div class="fw-semibold mb-1">Pencapaian SLA Respons</div><div class="fs-4 fw-bold">{{ $summary['response_rate'] === null ? 'Belum ada data' : $summary['response_rate'].'%' }}</div><div class="small text-secondary">{{ $summary['response_met'] }} dari {{ $summary['response_eligible'] }} tiket yang sudah direspons memenuhi SLA.</div></div></div></div>
    <div class="col-md-6"><div class="card stat h-100"><div class="card-body"><div class="fw-semibold mb-1">Pencapaian SLA Penyelesaian</div><div class="fs-4 fw-bold">{{ $summary['resolution_rate'] === null ? 'Belum ada data' : $summary['resolution_rate'].'%' }}</div><div class="small text-secondary">{{ $summary['resolution_met'] }} dari {{ $summary['resolution_eligible'] }} tiket yang sudah selesai memenuhi SLA.</div></div></div></div>
</div>

<div class="card content-card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0">Rincian Tiket</h5><span class="small text-secondary">{{ $tickets->total() }} data</span></div>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>No. Tiket</th><th>Tanggal</th><th>Judul</th><th>Unit</th><th>Kategori</th><th>Prioritas</th><th>Status</th><th>Teknisi</th><th>SLA Respons</th><th>SLA Penyelesaian</th></tr></thead>
        <tbody>
        @forelse($tickets as $ticket)
            @php
                $responseOk = $ticket->first_response_at && $ticket->response_due_at && $ticket->first_response_at->lessThanOrEqualTo($ticket->response_due_at);
                $completedAt = $ticket->resolved_at ?? $ticket->closed_at;
                $resolutionOk = $completedAt && $ticket->resolution_due_at && $completedAt->lessThanOrEqualTo($ticket->resolution_due_at);
            @endphp
            <tr>
                <td class="fw-semibold">{{ $ticket->ticket_number }}</td><td class="text-nowrap">{{ $ticket->created_at->format('d/m/Y H:i') }}</td><td>{{ $ticket->title }}</td><td>{{ $ticket->unit }}</td><td>{{ $ticket->category?->name ?? '-' }}</td><td><span class="text-uppercase">{{ $ticket->priority }}</span></td><td>{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</td><td>{{ $ticket->technician?->name ?? 'Belum ditugaskan' }}</td>
                <td>@if(!$ticket->response_due_at)<span class="text-secondary">-</span>@elseif(!$ticket->first_response_at)<span class="badge text-bg-warning">Belum respons</span>@elseif($responseOk)<span class="badge text-bg-success">Tercapai</span>@else<span class="badge text-bg-danger">Terlambat</span>@endif</td>
                <td>@if(!$ticket->resolution_due_at)<span class="text-secondary">-</span>@elseif(!$completedAt)<span class="badge text-bg-warning">Belum selesai</span>@elseif($resolutionOk)<span class="badge text-bg-success">Tercapai</span>@else<span class="badge text-bg-danger">Terlambat</span>@endif</td>
            </tr>
        @empty<tr><td colspan="10" class="text-center text-secondary py-4">Tidak ada tiket untuk filter yang dipilih.</td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $tickets->links() }}
</div></div>
@endsection
