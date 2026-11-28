@extends('layouts.app')
@section('title', 'Ubah Unit')
@section('heading', 'Ubah Unit Rumah Sakit')
@section('content')
<div class="card content-card"><div class="card-body">
<form method="POST" action="{{ route('units.update', $unit) }}">@csrf @method('PUT')
    <div class="mb-3"><label class="form-label">Nama unit / instalasi</label><input name="name" class="form-control" maxlength="120" value="{{ old('name', $unit->name) }}" required></div>
    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="3">{{ old('description', $unit->description) }}</textarea></div>
    <button class="btn btn-primary">Simpan Perubahan</button> <a href="{{ route('units.index') }}" class="btn btn-light">Batal</a>
</form>
</div></div>
@endsection
