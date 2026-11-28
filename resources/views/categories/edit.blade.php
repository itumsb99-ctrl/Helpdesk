@extends('layouts.app')
@section('title', 'Ubah Kategori')
@section('heading', 'Ubah Kategori')
@section('content')
<div class="card content-card"><div class="card-body">
<form method="POST" action="{{ route('categories.update', $category) }}">@csrf @method('PUT')
    <div class="mb-3"><label class="form-label">Nama kategori</label><input name="name" class="form-control" maxlength="100" value="{{ old('name', $category->name) }}" required></div>
    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea></div>
    <button class="btn btn-primary">Simpan Perubahan</button> <a href="{{ route('categories.index') }}" class="btn btn-light">Batal</a>
</form>
</div></div>
@endsection
