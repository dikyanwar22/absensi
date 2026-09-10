@extends('layouts.employee')
@section('title','Menu')
@section('content')
<h5 class="mb-1">Menu Karyawan</h5>
<p class="text-muted small mb-3">Akses cepat semua fitur self-service</p>

{{-- Info ringkas user --}}
<div class="card card-rounded p-3 mb-3 d-flex flex-row align-items-center gap-3">
    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Karyawan') }}&background=0d6efd&color=fff" class="rounded-circle" width="48" height="48">
    <div>
        <div class="fw-semibold">{{ auth()->user()->name ?? 'Karyawan' }}</div>
        <small class="text-muted">{{ auth()->user()->nik ?? '-' }} • {{ auth()->user()->display_role ?? 'STAFF' }}</small>
    </div>
    <a href="{{ route('employee.profile') }}" class="ms-auto btn btn-sm btn-outline-primary">Profile</a>
</div>

{{-- Grid Menu Utama --}}
<div class="row g-2 mb-3">
    <div class="col-4">
        <a href="{{ route('employee.home') }}" class="text-decoration-none">
            <div class="card card-rounded p-3 text-center h-100">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:48px;height:48px;background:#e7f1ff;color:#0d6efd;"><i class="bi bi-fingerprint fs-4"></i></div>
                <div class="fw-semibold small text-dark">Absen</div>
                <small class="text-muted" style="font-size:10px;">Masuk/Pulang</small>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('employee.leaves.create') }}" class="text-decoration-none">
            <div class="card card-rounded p-3 text-center h-100 border-primary" style="border:1px solid #0d6efd !important;">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:48px;height:48px;background:#0d6efd;color:#fff;"><i class="bi bi-calendar-plus fs-4"></i></div>
                <div class="fw-semibold small text-primary">Ajukan Cuti</div>
                <small class="text-muted" style="font-size:10px;">Cuti/Izin/Sakit</small>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('employee.leaves.index') }}" class="text-decoration-none">
            <div class="card card-rounded p-3 text-center h-100">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:48px;height:48px;background:#fff3cd;color:#b38600;"><i class="bi bi-card-list fs-4"></i></div>
                <div class="fw-semibold small text-dark">Cuti Saya</div>
                <small class="text-muted" style="font-size:10px;">Status approve</small>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('employee.history') }}" class="text-decoration-none">
            <div class="card card-rounded p-3 text-center h-100">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:48px;height:48px;background:#d1e7dd;color:#0f5132;"><i class="bi bi-calendar-check fs-4"></i></div>
                <div class="fw-semibold small text-dark">Riwayat</div>
                <small class="text-muted" style="font-size:10px;">Absensi</small>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('employee.payslip') }}" class="text-decoration-none">
            <div class="card card-rounded p-3 text-center h-100">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:48px;height:48px;background:#e0cffc;color:#553c9a;"><i class="bi bi-wallet2 fs-4"></i></div>
                <div class="fw-semibold small text-dark">Slip Gaji</div>
                <small class="text-muted" style="font-size:10px;">Download PDF</small>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('employee.profile') }}" class="text-decoration-none">
            <div class="card card-rounded p-3 text-center h-100">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:48px;height:48px;background:#f8d7da;color:#842029;"><i class="bi bi-person-badge fs-4"></i></div>
                <div class="fw-semibold small text-dark">Profil</div>
                <small class="text-muted" style="font-size:10px;">Data & sisa cuti</small>
            </div>
        </a>
    </div>
</div>

{{-- Menu sekunder / coming soon --}}
<div class="card card-rounded p-3 mb-3">
    <h6 class="mb-2 small fw-semibold">Lainnya</h6>
    <div class="d-flex flex-column gap-2">
        <a href="{{ route('employee.leaves.index') }}" class="d-flex justify-content-between align-items-center text-decoration-none p-2 rounded bg-light">
            <span class="small text-dark"><i class="bi bi-clock-history me-2"></i>Sisa Cuti & Status Kepegawaian</span><i class="bi bi-chevron-right text-muted"></i>
        </a>
        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light opacity-50">
            <span class="small text-dark"><i class="bi bi-arrow-left-right me-2"></i>Tukar Shift</span><small class="badge bg-secondary">Segera</small>
        </div>
        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light opacity-50">
            <span class="small text-dark"><i class="bi bi-pencil-square me-2"></i>Koreksi Absen</span><small class="badge bg-secondary">Segera</small>
        </div>
    </div>
</div>

@if(auth()->check() && in_array(auth()->user()->role, ['hrd','supervisor']))
<div class="card card-rounded p-3 mb-3" style="border:1px solid #0d6efd;">
    <h6 class="mb-2 small fw-semibold text-primary"><i class="bi bi-speedometer2 me-1"></i>Dashboard Admin</h6>
    <p class="small text-muted mb-2">Anda login sebagai <strong>{{ auth()->user()->display_role }}</strong>. Kembali ke panel admin untuk kelola karyawan & approve cuti.</p>
    <a href="/admin/dashboard" class="btn btn-primary btn-sm w-100"><i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard Admin</a>
</div>
@endif
<div class="text-center small text-muted">AbsensiKu v1.0 • Mobile PWA</div>
@endsection
