@extends('layouts.app')
@section('title','Detail Tiket')
@section('heading','Detail Tiket')
@section('content')
<div class="row g-3">
 <div class="col-lg-8"><div class="card content-card mb-3"><div class="card-body">
  <div class="d-flex justify-content-between flex-wrap gap-2"><div><div class="text-primary fw-bold">{{ $ticket->ticket_number }}</div><h5 class="fw-bold mt-1">{{ $ticket->title }}</h5></div><span class="badge text-bg-{{ $ticket->isOverdue()?'danger':'primary' }} align-self-start">{{ $ticket->isOverdue()?'SLA Terlambat':strtoupper($ticket->priority) }}</span></div>
  <p class="text-secondary mb-0">{{ $ticket->description }}</p>
  @include('tickets.attachments', ['ticket' => $ticket])
  <hr>
  <div class="row g-3 small"><div class="col-sm-6"><b>Pemohon</b><br>{{ $ticket->requester->name }}</div><div class="col-sm-6"><b>Unit</b><br>{{ $ticket->unit }}</div><div class="col-sm-6"><b>Kategori</b><br>{{ $ticket->category->name }}</div><div class="col-sm-6"><b>Lokasi</b><br>{{ $ticket->location }}</div><div class="col-sm-6"><b>Teknisi</b><br>{{ $ticket->technician->name ?? 'Belum ditugaskan' }}</div><div class="col-sm-6"><b>Status</b><br>{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</div><div class="col-sm-6"><b>Batas respons</b><br>{{ $ticket->response_due_at?->format('d/m/Y H:i') ?? '-' }}</div><div class="col-sm-6"><b>Batas penyelesaian</b><br>{{ $ticket->resolution_due_at?->format('d/m/Y H:i') ?? '-' }}</div></div>
 </div></div>
 <div class="card content-card"><div class="card-body"><h6 class="fw-bold">Catatan & komunikasi</h6>
 @forelse($ticket->comments as $comment)<div class="border rounded-3 p-3 mb-2"><div class="small fw-bold">{{ $comment->user->name }} <span class="text-secondary fw-normal">· {{ $comment->created_at->format('d/m/Y H:i') }}</span></div><div class="mt-2">{{ $comment->body }}</div></div>@empty<div class="text-secondary small mb-3">Belum ada catatan.</div>@endforelse
 <form method="POST" action="{{ route('tickets.comments',$ticket) }}">@csrf<textarea name="body" class="form-control mb-2" rows="3" placeholder="Tulis catatan atau informasi tambahan..." required></textarea><button class="btn btn-outline-primary btn-sm">Tambah Catatan</button></form>
 </div></div></div>
 <div class="col-lg-4">
 @if(auth()->user()->role==='admin')
 <div class="card content-card mb-3"><div class="card-body"><h6 class="fw-bold">Penugasan Teknisi</h6><form method="POST" action="{{ route('tickets.assign',$ticket) }}">@csrf<select class="form-select mb-2" name="technician_id" required><option value="">Pilih teknisi</option>@foreach($technicians as $tech)<option value="{{ $tech->id }}" @selected($ticket->technician_id==$tech->id)>{{ $tech->name }}</option>@endforeach</select><button class="btn btn-primary btn-sm w-100">Simpan Penugasan</button></form></div></div>
 @endif
 @if(in_array(auth()->user()->role,['admin','teknisi']) && (auth()->user()->role==='admin' || $ticket->technician_id===auth()->id()))
 <div class="card content-card"><div class="card-body"><h6 class="fw-bold">Update Penanganan</h6><form method="POST" action="{{ route('tickets.status',$ticket) }}">@csrf<select name="status" class="form-select mb-2">@foreach(['assigned','in_progress','pending','resolved','closed'] as $s)<option value="{{ $s }}" @selected($ticket->status===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select><textarea name="note" class="form-control mb-2" rows="3" placeholder="Catatan tindakan teknisi (opsional)"></textarea><button class="btn btn-success btn-sm w-100">Update Status</button></form></div></div>
 @endif
 <div class="card content-card mt-3"><div class="card-body">
  <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1"></i>Kronologi Aktivitas</h6>
  @forelse($histories as $history)
   <div class="d-flex gap-2 mb-3">
    <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px"><i class="bi bi-activity"></i></div>
    <div class="small border-bottom pb-2 flex-grow-1">
     <div class="fw-semibold">{{ str_replace('_',' ',ucfirst($history->event)) }}</div>
     <div class="text-secondary">{{ $history->user?->name ?? 'Sistem Otomatis' }} · {{ $history->created_at->format('d/m/Y H:i:s') }}</div>
     @if($history->old_value)<div class="mt-1"><span class="text-secondary">Sebelumnya:</span> {{ $history->old_value }}</div>@endif
     @if($history->new_value)<div><span class="text-secondary">Keterangan:</span> {{ $history->new_value }}</div>@endif
    </div>
   </div>
  @empty
   <div class="small text-secondary">Belum ada aktivitas.</div>
  @endforelse
  @if(auth()->user()->role === 'admin')
   <a href="{{ route('audit-logs.index', ['q' => $ticket->ticket_number]) }}" class="btn btn-outline-primary btn-sm w-100 mt-2">Lihat di semua riwayat aktivitas</a>
  @endif
 </div></div>
 </div>
</div>
@endsection
