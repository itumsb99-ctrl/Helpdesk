@extends('layouts.app')
@section('title','Daftar Tiket')
@section('heading','Manajemen Tiket')
@section('content')
<div class="card content-card"><div class="card-body">
 <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><form class="d-flex flex-wrap gap-2"><input class="form-control" name="q" placeholder="Cari nomor / judul tiket" value="{{ request('q') }}"><select class="form-select" name="status"><option value="">Semua status</option>@foreach(['open','assigned','in_progress','pending','resolved','closed'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select><select class="form-select" name="priority"><option value="">Semua prioritas</option>@foreach(['critical','high','medium','low'] as $p)<option value="{{ $p }}" @selected(request('priority')===$p)>{{ ucfirst($p) }}</option>@endforeach</select><button class="btn btn-outline-primary">Cari</button></form><a href="{{ route('tickets.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Tiket</a></div>
 <div class="table-responsive"><table class="table align-middle"><thead><tr><th>No. Tiket</th><th>Judul</th><th>Pemohon</th><th>Teknisi</th><th>Prioritas</th><th>Status</th><th>SLA</th><th></th></tr></thead><tbody>
 @forelse($tickets as $t)<tr><td class="fw-semibold">{{ $t->ticket_number }}</td><td>{{ $t->title }}</td><td>{{ $t->requester->name }}</td><td>{{ $t->technician->name ?? 'Belum ditugaskan' }}</td><td>{{ strtoupper($t->priority) }}</td><td>{{ ucfirst(str_replace('_',' ',$t->status)) }}</td><td>@if($t->isOverdue())<span class="badge text-bg-danger">Terlambat</span>@else<span class="badge text-bg-success">Dalam batas</span>@endif</td><td><a href="{{ route('tickets.show',$t) }}" class="btn btn-sm btn-outline-primary">Detail</a></td></tr>
 @empty<tr><td colspan="8" class="text-center text-secondary py-4">Data tiket tidak ditemukan.</td></tr>@endforelse
 </tbody></table></div>{{ $tickets->links() }}
</div></div>
@endsection
