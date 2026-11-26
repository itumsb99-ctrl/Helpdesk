@extends('layouts.app')
@section('title','Login')
@section('guest')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3" style="background:linear-gradient(135deg,#10243e,#17639a)">
 <div class="card border-0 shadow-lg rounded-4 p-4" style="width:100%;max-width:420px">
  <div class="text-center mb-4"><div class="display-6 text-primary"><i class="bi bi-headset"></i></div><h3 class="fw-bold mb-1">IT Helpdesk</h3><div class="text-secondary">Rumah Sakit</div></div>
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
  <form method="POST" action="{{ route('login.process') }}">@csrf
   <div class="mb-3"><label class="form-label">Email</label><input class="form-control form-control-lg" type="email" name="email" value="{{ old('email') }}" required autofocus></div>
   <div class="mb-3"><label class="form-label">Password</label><input class="form-control form-control-lg" type="password" name="password" required></div>
   <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" value="1" id="remember"><label for="remember" class="form-check-label">Ingat saya</label></div>
   <button class="btn btn-primary btn-lg w-100">Masuk</button>
  </form>
  <div class="small text-secondary mt-3">Gunakan akun yang diberikan administrator.</div>
 </div>
</div>
@endsection
