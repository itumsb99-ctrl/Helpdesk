@extends('layouts.app')
@section('title','Ubah Pengguna')
@section('heading','Ubah Data Pengguna')
@section('content')
<div class="card content-card"><div class="card-body">
<form method="POST" action="{{ route('users.update', $user) }}">@csrf @method('PUT')<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nomor Pegawai</label><input name="employee_id" class="form-control text-uppercase" value="{{ old('employee_id', $user->employee_id) }}" placeholder="Contoh: SBA521" pattern="SBA[0-9]{3,}" maxlength="20" required><div class="form-text">Format: SBA diikuti minimal 3 angka.</div></div>
<div class="col-md-6"><label class="form-label">Nama lengkap</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" maxlength="120" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" maxlength="180" required></div>
<div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="pegawai" @selected(old('role', $user->role)==='pegawai')>Pegawai</option><option value="teknisi" @selected(old('role', $user->role)==='teknisi')>Teknisi</option><option value="admin" @selected(old('role', $user->role)==='admin')>Admin</option></select>@if(auth()->id()===$user->id)<div class="form-text">Role akun yang sedang digunakan tidak dapat diubah.</div>@endif</div>
<div class="col-md-6"><label class="form-label">Unit / Instalasi</label><input name="unit" class="form-control" value="{{ old('unit', $user->unit) }}" maxlength="120"></div>
<div class="col-md-6"><label class="form-label">Password baru <span class="text-secondary">(opsional)</span></label><input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password"><div class="form-text">Kosongkan jika tidak ingin mengubah password.</div></div>
<div class="col-md-6"><label class="form-label">Konfirmasi password baru</label><input type="password" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password"></div>
</div><div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Simpan Perubahan</button><a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a></div></form>
</div></div>
@endsection
