@extends('layouts.app')
@section('title','Buat Tiket')
@section('heading','Buat Tiket Baru')
@section('content')
<div class="card content-card"><div class="card-body p-4">
<form method="POST" action="{{ route('tickets.store') }}">@csrf
<div class="row g-3">
 <div class="col-md-8"><label class="form-label">Judul gangguan</label><input name="title" class="form-control" value="{{ old('title') }}" required></div>
 <div class="col-md-4"><label class="form-label">Kategori</label><select name="category_id" class="form-select" required><option value="">Pilih kategori</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id')==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
 <div class="col-md-4"><label class="form-label">Prioritas</label><select name="priority" class="form-select" required>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','critical'=>'Critical'] as $v=>$label)<option value="{{ $v }}" @selected(old('priority','medium')===$v)>{{ $label }}</option>@endforeach</select></div>
 <div class="col-md-4"><label class="form-label">Unit / Instalasi</label><input name="unit" class="form-control" value="{{ old('unit',auth()->user()->unit) }}" required></div>
 <div class="col-md-4"><label class="form-label">Lokasi</label><input name="location" class="form-control" placeholder="Contoh: Poli Umum, Lantai 1" value="{{ old('location') }}" required></div>
 <div class="col-12"><label class="form-label">Deskripsi gangguan</label><textarea name="description" rows="5" class="form-control" required>{{ old('description') }}</textarea></div>
</div>
<div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Kirim Tiket</button><a href="{{ route('tickets.index') }}" class="btn btn-light">Batal</a></div>
</form></div></div>
@endsection
