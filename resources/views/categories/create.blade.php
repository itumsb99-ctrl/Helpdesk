@extends('layouts.app')
@section('title', 'Tambah Kategori')
@section('heading', 'Tambah Kategori')
@section('content')
<div class="card content-card"><div class="card-body">
<form method="POST" action="{{ route('categories.store') }}">@csrf
    <div class="mb-3"><label class="form-label">Nama kategori</label><input name="name" class="form-control" maxlength="100" value="{{ old('name') }}" required></div>
    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea></div>
    <button class="btn btn-primary">Simpan</button> <a href="{{ route('categories.index') }}" class="btn btn-light">Batal</a>
</form>
</div></div>
@endsection
