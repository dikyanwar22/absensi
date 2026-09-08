@extends('layouts.employee')
@section('title','Cuti Saya')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Pengajuan Cuti/Izin</h5>
    <a href="{{ route('employee.leaves.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Ajukan</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@forelse($leaves as $l)
<div class="card card-rounded p-3 mb-2">
    <div class="d-flex justify-content-between">
        <div style="flex:1;">
            <div class="fw-semibold">{{ $l->leaveType->name }} - {{ $l->total_days }} hari</div>
            <small class="text-muted">{{ $l->start_date }} s/d {{ $l->end_date }}</small>
            <div class="small">{{ $l->reason }}</div>
            <div class="small text-muted mt-1">
                <i class="bi bi-person-badge"></i> Atasan: {{ $l->supervisor->name ?? '-' }}|{{ $l->supervisor ? ucfirst($l->supervisor->role) : '-' }}|{{ $l->supervisor->nik ?? '-' }}
                @if($l->backupUser) <br><i class="bi bi-people"></i> Backup: {{ $l->backupUser->name }}|{{ ucfirst($l->backupUser->role) }}|{{ $l->backupUser->nik ?? '-' }} @endif
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
