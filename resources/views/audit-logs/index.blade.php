@extends('layouts.app')
@section('title', 'Riwayat Aktivitas')
@section('heading', 'Riwayat Aktivitas')
@section('content')
<div class="card content-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Nomor / judul tiket</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="HD-... atau kata kunci">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Jenis aktivitas</label>
                <select name="event" class="form-select">
                    <option value="">Semua aktivitas</option>
                    @foreach(['created'=>'Tiket dibuat','assigned'=>'Penugasan teknisi','status'=>'Perubahan status','comment'=>'Komentar','attachment_uploaded'=>'Lampiran diunggah','attachment_deleted'=>'Lampiran dihapus','sla_escalated'=>'Eskalasi SLA'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('event') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Pelaku</label>
                <input type="text" name="actor" class="form-control" value="{{ request('actor') }}" placeholder="Nama / SBA">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Dari tanggal</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Sampai tanggal</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-primary"><i class="bi bi-search"></i></button>
            </div>
            <div class="col-12">
                <a href="{{ route('audit-logs.index') }}" class="small text-decoration-none">Reset filter</a>
            </div>
        </form>
    </div>
</div>

<div class="card content-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Catatan aktivitas sistem</h6>
            <span class="badge text-bg-light border">{{ $histories->total() }} aktivitas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Tiket</th>
                        <th>Aktivitas</th>
                        <th>Pelaku</th>
                        <th>Detail perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr>
                            <td class="text-nowrap small">{{ $history->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if($history->ticket)
                                    <a href="{{ route('tickets.show', $history->ticket) }}" class="fw-semibold text-decoration-none">{{ $history->ticket->ticket_number }}</a>
                                    <div class="small text-secondary">{{ $history->ticket->title }}</div>
                                @else
                                    <span class="text-muted">Tiket tidak tersedia</span>
                                @endif
                            </td>
                            <td><span class="badge text-bg-info">{{ str_replace('_', ' ', ucfirst($history->event)) }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $history->user?->name ?? 'Sistem' }}</div>
                                <div class="small text-secondary">{{ $history->user?->employee_id ?? ($history->user?->role ?? 'Otomatis') }}</div>
                            </td>
                            <td class="small">
                                @if($history->old_value)
                                    <div><span class="text-secondary">Sebelumnya:</span> {{ $history->old_value }}</div>
                                @endif
                                @if($history->new_value)
                                    <div><span class="text-secondary">Menjadi / keterangan:</span> {{ $history->new_value }}</div>
                                @endif
                                @if(!$history->old_value && !$history->new_value)
                                    <span class="text-muted">Tidak ada detail tambahan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada aktivitas yang sesuai dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $histories->links() }}
    </div>
</div>
@endsection
