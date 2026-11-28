@extends('layouts.app')
@section('title','Pengguna')
@section('heading','Manajemen Pengguna')
@section('content')
<div class="card content-card"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h6 class="fw-bold mb-1">Daftar Pengguna</h6><div class="small text-secondary">Kelola akun Admin, Teknisi, dan Pegawai.</div></div>
        <a class="btn btn-primary btn-sm" href="{{ route('users.create') }}"><i class="bi bi-person-plus me-1"></i>Tambah Pengguna</a>
    </div>
    <form method="GET" action="{{ route('users.index') }}" class="row g-2 mb-3">
        <div class="col-md-5"><input name="q" class="form-control" placeholder="Cari nomor pegawai, nama, email, atau unit..." value="{{ request('q') }}"></div>
        <div class="col-md-3"><select name="role" class="form-select"><option value="">Semua Role</option><option value="admin" @selected(request('role')==='admin')>Admin</option><option value="teknisi" @selected(request('role')==='teknisi')>Teknisi</option><option value="pegawai" @selected(request('role')==='pegawai')>Pegawai</option></select></div>
        <div class="col-md-2"><select name="status" class="form-select"><option value="">Semua Status</option><option value="active" @selected(request('status')==='active')>Aktif</option><option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option></select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-outline-primary w-100">Filter</button><a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a></div>
    </form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nomor Pegawai</th><th>Nama</th><th>Email</th><th>Role</th><th>Unit / Instalasi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
    @forelse($users as $u)
        <tr><td class="fw-semibold">{{ $u->employee_id }}</td><td class="fw-semibold">{{ $u->name }}</td><td>{{ $u->email }}</td><td><span class="badge text-bg-light border text-capitalize">{{ $u->role }}</span></td><td>{{ $u->unit ?: '-' }}</td><td><span class="badge {{ $u->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td class="text-end text-nowrap"><a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i> Ubah</a>
            @if(auth()->id() !== $u->id)<form method="POST" action="{{ route('users.toggle', $u) }}" class="d-inline" onsubmit="return confirm('Yakin ingin {{ $u->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun ini?')">@csrf @method('PATCH')<button class="btn btn-sm {{ $u->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>@endif</td>
        </tr>
    @empty<tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada pengguna yang sesuai.</td></tr>@endforelse
    </tbody></table></div>
    {{ $users->links() }}
</div></div>
@endsection
