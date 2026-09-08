@extends('layouts.employee')
@section('title','Notifikasi')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-bell-fill me-2 text-primary"></i>Notifikasi</h5>
    <span class="badge bg-primary rounded-pill">{{ $notifications->count() }} total</span>
</div>

@if($notifications->isEmpty())
<div class="card card-rounded p-5 text-center">
    <div class="mb-3"><i class="bi bi-bell-slash fs-1 text-muted"></i></div>
    <h6 class="text-muted">Tidak ada notifikasi</h6>
    <p class="small text-muted mb-0">Semua aktivitas terkait Anda akan muncul di sini:<br>cuti, slip gaji, absensi, pengumuman, dan kontrak.</p>
    <a href="{{ route('employee.home') }}" class="btn btn-primary btn-sm mt-3">Kembali ke Home</a>
</div>
@else
<div class="d-flex gap-2 mb-3">
    <span class="badge bg-light text-dark border"><i class="bi bi-funnel me-1"></i>{{ $notifications->count() }} notifikasi terbaru</span>
    <a href="{{ route('employee.home') }}" class="ms-auto small text-decoration-none">Ke Home →</a>
</div>

<div class="d-flex flex-column gap-2">
@forelse($notifications as $n)
<a href="{{ $n['link'] }}" class="text-decoration-none">
    <div class="card card-rounded p-3 d-flex flex-row gap-3 align-items-start" style="border-left:4px solid var(--bs-{{ $n['color'] }});">
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:44px;height:44px;background:{{ $n['bg'] }};color:var(--bs-{{ $n['color'] }});">
            <i class="bi {{ $n['icon'] }} fs-5"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="fw-semibold small text-dark" style="line-height:1.3;">{{ $n['title'] }}</div>
                <span class="badge bg-{{ $n['color'] }} flex-shrink-0" style="font-size:9px;">{{ $n['badge'] }}</span>
            </div>
            <div class="small text-muted mt-1" style="line-height:1.4;">{{ $n['desc'] }}</div>
            <div class="small text-muted mt-1" style="font-size:11px;"><i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($n['time'])->diffForHumans() }} • {{ \Carbon\Carbon::parse($n['time'])->format('d M Y H:i') }}</div>
        </div>
        <i class="bi bi-chevron-right text-muted flex-shrink-0 mt-2"></i>
    </div>
</a>
@empty
<div class="card card-rounded p-4 text-center text-muted">Tidak ada notifikasi</div>
@endforelse
</div>
<p class="small text-muted text-center mt-3">Notifikasi di-generate otomatis dari data Anda (cuti, gaji, absen, pengumuman).</p>
@endif
@endsection
