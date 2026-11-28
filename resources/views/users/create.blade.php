@extends('layouts.app')
@section('title','Tambah Pengguna')
@section('heading','Tambah Pengguna')
@section('content')
<div class="card content-card"><div class="card-body">
<form method="POST" action="{{ route('users.store') }}">@csrf<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama lengkap</label><input name="name" class="form-control" value="{{ old('name') }}" maxlength="120" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="180" required></div>
<div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="pegawai" @selected(old('role','pegawai')==='pegawai')>Pegawai</option><option value="teknisi" @selected(old('role')==='teknisi')>Teknisi</option><option value="admin" @selected(old('role')==='admin')>Admin</option></select></div>
<div class="col-md-6"><label class="form-label">Unit / Instalasi</label><input name="unit" class="form-control" value="{{ old('unit') }}" maxlength="120"></div>
<div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" minlength="8" required><div class="form-text">Minimal 8 karakter.</div></div>
<div class="col-md-6"><label class="form-label">Konfirmasi password</label><input type="password" name="password_confirmation" class="form-control" minlength="8" required></div>
</div><div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Simpan Pengguna</button><a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a></div></form>
</div></div>
@endsection
