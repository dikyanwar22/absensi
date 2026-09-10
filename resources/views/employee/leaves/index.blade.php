@extends('layouts.employee')
@section('title','Cuti Saya')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Pengajuan Cuti/Izin</h5>
    <a href="{{ route('employee.leaves.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Ajukan</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

{{-- Filter Tanggal 01-31 bulan ini (default) --}}
<div class="card card-rounded p-3 mb-3">
    <form method="GET" action="{{ route('employee.leaves.index') }}" class="row g-2 align-items-end">
        <div class="col-5">
            <label class="form-label small mb-1">Dari</label>
            <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-5">
            <label class="form-label small mb-1">Sampai</label>
            <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-2 d-grid">
            <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        </div>
        <div class="col-12 d-flex gap-2 mt-2 flex-wrap">
            <a href="{{ route('employee.leaves.index', ['start_date'=>\Carbon\Carbon::now()->startOfMonth()->toDateString(), 'end_date'=>\Carbon\Carbon::now()->endOfMonth()->toDateString()]) }}" class="btn btn-sm {{ $start==\Carbon\Carbon::now()->startOfMonth()->toDateString() && $end==\Carbon\Carbon::now()->endOfMonth()->toDateString() ? 'btn-primary' : 'btn-outline-primary' }}">Bulan Ini</a>
            <a href="{{ route('employee.leaves.index', ['start_date'=>\Carbon\Carbon::now()->startOfWeek()->toDateString(), 'end_date'=>\Carbon\Carbon::now()->endOfWeek()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">Minggu Ini</a>
            <a href="{{ route('employee.leaves.index', ['start_date'=>\Carbon\Carbon::now()->subMonths(3)->startOfMonth()->toDateString(), 'end_date'=>\Carbon\Carbon::now()->endOfMonth()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">3 Bulan</a>
            <a href="{{ route('employee.leaves.index') }}" class="btn btn-sm btn-outline-dark ms-auto">Reset</a>
        </div>
    </form>
    <small class="text-muted d-block mt-2">Menampilkan {{ \Carbon\Carbon::parse($start)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($end)->translatedFormat('d M Y') }} — {{ $leaves->total() }} data</small>
</div>

@forelse($leaves as $l)
<div class="card card-rounded p-3 mb-2">
    <div class="d-flex justify-content-between">
        <div style="flex:1;">
            <div class="fw-semibold">{{ $l->leaveType->name }} - {{ $l->total_days }} hari</div>
            <small class="text-muted">{{ $l->start_date }} s/d {{ $l->end_date }}</small>
            <div class="small">{{ $l->reason }}</div>
            <div class="small text-muted mt-1">
                <i class="bi bi-person-badge"></i> Atasan: {{ $l->supervisor->name ?? '-' }}|{{ $l->supervisor ? $l->supervisor->display_role : '-' }}|{{ $l->supervisor->nik ?? '-' }}
                @if($l->backupUser) <br><i class="bi bi-people"></i> Backup: {{ $l->backupUser->name }}|{{ $l->backupUser->display_role }}|{{ $l->backupUser->nik ?? '-' }} @endif
            </div>
        </div>
        <div class="text-end ms-2">
            @if($l->final_status=='pending')
                <span class="badge bg-warning">Menunggu {{ $l->status_supervisor=='pending' ? 'Supervisor' : 'HRD' }}</span>
            @elseif($l->final_status=='approved')
                <span class="badge bg-success">Disetujui</span>
            @else
                <span class="badge bg-danger">Ditolak</span>
            @endif
            <div class="small text-muted mt-1">SPV: {{ $l->status_supervisor }} | HRD: {{ $l->status_hrd }}</div>
        </div>
    </div>
</div>
@empty
<div class="card card-rounded p-4 text-center text-muted">Belum ada pengajuan</div>
@endforelse
<div class="mt-3">{{ $leaves->links() }}</div>
@endsection
