<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title','IT Helpdesk') - Rumah Sakit</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>body{background:#f4f7fb;color:#243247}.sidebar{width:250px;min-height:100vh;background:#10243e;color:#fff}.sidebar a{display:block;color:#d9e4f2;text-decoration:none;padding:11px 14px;border-radius:8px;margin:3px 0}.sidebar a:hover,.sidebar a.active{background:#1d4269;color:#fff}.brand{font-weight:800;letter-spacing:.5px}.main{min-width:0}.stat{border:0;border-radius:14px;box-shadow:0 4px 18px #17324d0c}.table thead th{font-size:.82rem;color:#607086;background:#f8fafc}.content-card{border:0;border-radius:14px;box-shadow:0 4px 18px #17324d0c}@media(max-width:767px){.sidebar{width:100%;min-height:auto}.sidebar .navlinks{display:flex;overflow:auto}.sidebar .navlinks a{white-space:nowrap}.app-shell{display:block!important}}</style>
</head><body>
@if(auth()->check())
<div class="d-flex app-shell"><aside class="sidebar p-3">
<div class="brand fs-5 mb-1"><i class="bi bi-headset me-2"></i>IT HELPDESK</div><div class="small text-white-50 mb-4">Rumah Sakit</div>
<div class="small text-uppercase text-white-50 mb-2">Menu Utama</div><nav class="navlinks">
<a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard')?'active':'' }}"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*')?'active':'' }}"><i class="bi bi-ticket-detailed me-2"></i>Tiket</a>
@if(auth()->user()->role==='admin')<a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*')?'active':'' }}"><i class="bi bi-people me-2"></i>Pengguna</a>@endif
<a href="{{ route('reports.tickets.index') }}" class="{{ request()->routeIs('reports.tickets.*')?'active':'' }}"><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan Tiket</a></nav><div class="mt-4 pt-3 border-top border-secondary small text-white-50">Login sebagai</div><div class="fw-semibold">{{ auth()->user()->name }}</div><div class="small text-white-50 text-capitalize mb-3">{{ auth()->user()->role }}</div>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button></form>
</aside><main class="main flex-grow-1 p-3 p-lg-4">
<div class="d-flex justify-content-between align-items-center mb-4"><div><div class="text-secondary small">Sistem Layanan Teknologi Informasi</div><h4 class="mb-0 fw-bold">@yield('heading','Dashboard')</h4></div>
<div class="d-flex align-items-center gap-2"><div class="dropdown"><button class="btn btn-light border position-relative" data-bs-toggle="dropdown" title="Notifikasi"><i class="bi bi-bell"></i>@if(auth()->user()->unreadNotifications()->count())<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ auth()->user()->unreadNotifications()->count() }}</span>@endif</button>
<div class="dropdown-menu dropdown-menu-end p-2 shadow" style="width:min(360px,90vw)"><div class="fw-semibold px-2 py-1">Notifikasi SLA terbaru</div>
@forelse(auth()->user()->notifications()->latest()->limit(5)->get() as $notification)<a class="dropdown-item rounded py-2 {{ $notification->read_at ? '' : 'bg-light' }}" href="{{ $notification->data['url'] ?? route('dashboard') }}"><div class="fw-semibold small">{{ $notification->data['title'] ?? 'Notifikasi' }}</div><div class="small text-secondary text-wrap">{{ $notification->data['message'] ?? '' }}</div><div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div></a>
@empty<div class="small text-secondary px-2 py-3">Belum ada notifikasi.</div>@endforelse</div></div><span class="badge text-bg-light border p-2">{{ now()->translatedFormat('d M Y') }}</span></div></div>
@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')</main></div>
@else @yield('guest') @endif
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>@stack('scripts')
</body></html>