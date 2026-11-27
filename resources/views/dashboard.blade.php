@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard IT Helpdesk')

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['Total Tiket', $counts['total'], 'bi-ticket-perforated', 'primary'],
        ['Tiket Baru', $counts['open'], 'bi-inbox', 'info'],
        ['Sedang Diproses', $counts['progress'], 'bi-gear', 'warning'],
        ['Selesai', $counts['resolved'], 'bi-check-circle', 'success'],
        ['Mendekati SLA Respons', $counts['response_warning'], 'bi-hourglass-split', 'warning'],
        ['Mendekati SLA Penyelesaian', $counts['resolution_warning'], 'bi-clock-history', 'warning'],
        ['Tiket Lewat SLA', $counts['overdue'], 'bi-alarm', 'danger'],
    ] as $stat)
        <div class="col-6 col-md-4 col-xl">
            <div class="card stat h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <span class="text-secondary small">{{ $stat[0] }}</span>
                        <i class="bi {{ $stat[2] }} text-{{ $stat[3] }} fs-5"></i>
                    </div>
                    <div class="fs-2 fw-bold mt-2">{{ $stat[1] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card content-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary small">Terlambat memberikan respons</span>
                    <span class="badge text-bg-danger">Respons</span>
                </div>
                <div class="fs-3 fw-bold text-danger mt-2">{{ $counts['response_overdue'] }}</div>
                <div class="small text-secondary">Tiket aktif yang belum mendapat respons teknisi setelah batas SLA.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card content-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary small">Terlambat diselesaikan</span>
                    <span class="badge text-bg-danger">Penyelesaian</span>
                </div>
                <div class="fs-3 fw-bold text-danger mt-2">{{ $counts['resolution_overdue'] }}</div>
                <div class="small text-secondary">Tiket aktif yang melewati batas waktu penyelesaian SLA.</div>
            </div>
        </div>
    </div>
</div>

@if($slaMonitorTickets->isNotEmpty())
    <div class="card content-card border-start border-warning border-4 mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h6 class="fw-bold mb-1"><i class="bi bi-shield-exclamation me-2 text-warning"></i>Monitoring SLA</h6>
                    <div class="small text-secondary">Menampilkan tiket yang mendekati batas SLA dalam 30 menit atau sudah melewati batas.</div>
                </div>
                <span class="badge text-bg-secondary">{{ $slaMonitorTickets->count() }} tiket ditampilkan</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Judul</th>
                            <th>Teknisi</th>
                            <th>Prioritas</th>
                            <th>Monitoring</th>
                            <th>Batas SLA</th>
                            <th>Selisih waktu</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slaMonitorTickets as $ticket)
                            @foreach($ticket->sla_monitoring as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $ticket->ticket_number }}</td>
                                    <td>{{ $ticket->title }}</td>
                                    <td>{{ $ticket->technician?->name ?? 'Belum ditugaskan' }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ ['critical' => 'danger', 'high' => 'warning', 'medium' => 'primary', 'low' => 'secondary'][$ticket->priority] ?? 'secondary' }}">
                                            {{ strtoupper($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $item['state'] === 'overdue' ? 'danger' : 'warning' }}">
                                            {{ $item['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ $item['due_at']->format('d/m/Y H:i') }}</td>
                                    <td class="{{ $item['state'] === 'overdue' ? 'text-danger fw-semibold' : 'text-warning-emphasis' }}">
                                        {{ $item['state'] === 'overdue' ? 'Terlambat ' : 'Sisa ' }}{{ $item['duration'] }}
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('tickets.show', $ticket) }}">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-check-circle-fill"></i>
        <div>Tidak ada tiket yang mendekati batas SLA 30 menit atau melewati batas SLA.</div>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card content-card">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Tren Tiket 7 Hari Terakhir</h6>
                <canvas id="trendChart" height="105"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card content-card">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Status Tiket</h6>
                <canvas id="statusChart" height="190"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card content-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Tiket Terbaru</h6>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>No. Tiket</th>
                        <th>Judul</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="fw-semibold">{{ $ticket->ticket_number }}</td>
                            <td>{{ $ticket->title }}</td>
                            <td>
                                <span class="badge text-bg-{{ ['critical' => 'danger', 'high' => 'warning', 'medium' => 'primary', 'low' => 'secondary'][$ticket->priority] ?? 'secondary' }}">
                                    {{ strtoupper($ticket->priority) }}
                                </span>
                            </td>
                            <td>{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</td>
                            <td><a class="btn btn-sm btn-light" href="{{ route('tickets.show', $ticket) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada tiket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($trend->pluck('day')),
        datasets: [{
            label: 'Tiket',
            data: @json($trend->pluck('total')),
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,.12)',
            fill: true,
            tension: .35
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: @json($statusData->map(fn ($item) => $statusLabels[$item->status] ?? $item->status)),
        datasets: [{
            data: @json($statusData->pluck('total')),
            backgroundColor: ['#2563eb', '#0ea5e9', '#f59e0b', '#8b5cf6', '#10b981', '#64748b']
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
