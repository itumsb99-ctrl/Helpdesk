@extends('layouts.app')
@section('title', 'Manajemen Unit')
@section('heading', 'Manajemen Unit Rumah Sakit')
@section('content')
<div class="card content-card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h6 class="fw-bold mb-1">Daftar Unit / Instalasi</h6><div class="small text-secondary">Unit nonaktif tidak tersedia pada formulir tiket baru.</div></div>
        <a href="{{ route('units.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Unit</a>
    </div>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Nama Unit</th><th>Deskripsi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($units as $unit)
            <tr>
                <td class="fw-semibold">{{ $unit->name }}</td>
                <td>{{ $unit->description ?: '-' }}</td>
                <td><span class="badge {{ $unit->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('units.edit', $unit) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                    <form method="POST" action="{{ route('units.toggle', $unit) }}" class="d-inline">@csrf @method('PATCH')
                        <button class="btn btn-outline-{{ $unit->is_active ? 'danger' : 'success' }} btn-sm" onclick="return confirm('Ubah status unit ini?')">{{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada unit.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $units->links() }}
</div></div>
@endsection
