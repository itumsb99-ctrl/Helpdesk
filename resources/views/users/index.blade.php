@extends('layouts.app')
@section('title','Pengguna')
@section('heading','Manajemen Pengguna')
@section('content')
<div class="card content-card"><div class="card-body"><div class="d-flex justify-content-between mb-3"><h6 class="fw-bold">Daftar akun</h6><a class="btn btn-primary btn-sm" href="{{ route('users.create') }}">Tambah Pengguna</a></div><div class="table-responsive"><table class="table"><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Unit</th><th>Status</th></tr></thead><tbody>@foreach($users as $u)<tr><td>{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ ucfirst($u->role) }}</td><td>{{ $u->unit }}</td><td>{{ $u->is_active?'Aktif':'Nonaktif' }}</td></tr>@endforeach</tbody></table></div>{{ $users->links() }}</div></div>
@endsection
