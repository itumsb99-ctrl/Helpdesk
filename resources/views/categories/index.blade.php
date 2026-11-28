@extends('layouts.app')
@section('title', 'Manajemen Kategori')
@section('heading', 'Manajemen Kategori')
@section('content')
<div class="card content-card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h6 class="fw-bold mb-1">Daftar Kategori Gangguan</h6><div class="small text-secondary">Kategori yang nonaktif tidak dapat dipilih saat membuat tiket.</div></div>
        <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Kategori</a>
    </div>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Nama Kategori</th><th>Deskripsi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($categories as $category)
            <tr>
                <td class="fw-semibold">{{ $category->name }}</td>
                <td>{{ $category->description ?: '-' }}</td>
                <td><span class="badge {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                    <form method="POST" action="{{ route('categories.toggle', $category) }}" class="d-inline">@csrf @method('PATCH')
                        <button class="btn btn-outline-{{ $category->is_active ? 'danger' : 'success' }} btn-sm" onclick="return confirm('Ubah status kategori ini?')">{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada kategori.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $categories->links() }}
</div></div>
@endsection
