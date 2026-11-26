@extends('layouts.app')
@section('title','Tambah Pengguna')
@section('heading','Tambah Pengguna')
@section('content')
<div class="card content-card"><div class="card-body"><form method="POST" action="{{ route('users.store') }}">@csrf<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama lengkap</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
<div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="pegawai">Pegawai</option><option value="teknisi">Teknisi</option><option value="admin">Admin</option></select></div>
<div class="col-md-6"><label class="form-label">Unit / Instalasi</label><input name="unit" class="form-control" value="{{ old('unit') }}"></div>
<div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Konfirmasi password</label><input type="password" name="password_confirmation" class="form-control" required></div>
</div><button class="btn btn-primary mt-4">Simpan Pengguna</button></form></div></div>
@endsection
