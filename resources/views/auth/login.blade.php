@extends('layouts.app')
@section('title','Login')
@section('guest')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3" style="background:linear-gradient(135deg,#eaf5e8,#dff5f2)">
 <div class="card border-0 shadow-lg rounded-4 p-4" style="width:100%;max-width:420px">
  <div class="text-center mb-4"><img src="{{ asset('images/unimedika-logo.jpg') }}" alt="Logo Rumah Sakit UniMedika Setu Bekasi" style="width:112px;height:112px;object-fit:contain;margin-bottom:12px"><h3 class="fw-bold mb-1">IT Helpdesk</h3><div class="fw-semibold" style="color:#078b91">Rumah Sakit UniMedika Setu Bekasi</div><div class="small text-secondary mt-1">Sistem Layanan Teknologi Informasi</div></div>
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
  <form method="POST" action="{{ route('login.process') }}">@csrf
   <div class="mb-3"><label class="form-label">Email</label><input class="form-control form-control-lg" type="email" name="email" value="{{ old('email') }}" required autofocus></div>
   <div class="mb-3"><label class="form-label">Password</label><input class="form-control form-control-lg" type="password" name="password" required></div>
   <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" value="1" id="remember"><label for="remember" class="form-check-label">Ingat saya</label></div>
   <button class="btn btn-primary btn-lg w-100">Masuk</button>
  </form>
  <div class="small text-secondary mt-3">Gunakan akun yang diberikan administrator Rumah Sakit UniMedika.</div>
 </div>
</div>
@endsection
