@extends('layouts.admin')
@section('title','Pengajuan Cuti')
@section('header','Pengajuan Cuti / Izin / Sakit')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

@php
    $role = auth()->user()->role;
    $filter = request('filter');
@endphp
@if($role==='supervisor')
<div class="alert alert-info py-2" style="font-size:12px;">
    <i class="fas fa-info-circle"></i> <strong>Alur 2 Level:</strong> Karyawan <i class="fas fa-arrow-right"></i> Atasan (Anda) Approve Lv1 <i class="fas fa-arrow-right"></i> HRD Final Approve Lv2.
    Hanya cuti yang <b>memilih Anda sebagai Atasan</b> (atau 1 departemen yang sama untuk data lama) dan masih <b>Pending Supervisor</b> yang tampil di sini. Departemen Anda: <b>{{ auth()->user()->employee->department->name ?? '-' }}</b>.
</div>
@else
<div class="alert alert-warning py-2" style="font-size:12px;">
    <i class="fas fa-info-circle"></i> <strong>Alur HRD:</strong> Supervisor Approve dulu <i class="fas fa-arrow-right"></i> Baru HRD bisa <b>Final Approve</b>. Filter untuk memudahkan.
</div>
<div class="mb-2">
    <a href="{{ route('admin.leaves.index') }}" class="btn btn-sm {{ !$filter ? 'btn-primary' : 'btn-outline-primary' }}">Semua ({{ \App\Models\Leave::count() }})</a>
    <a href="{{ route('admin.leaves.index', ['filter'=>'pending_spv']) }}" class="btn btn-sm {{ $filter==='pending_spv' ? 'btn-warning' : 'btn-outline-warning' }}">Menunggu SPV ({{ \App\Models\Leave::where('status_supervisor','pending')->count() }})</a>
    <a href="{{ route('admin.leaves.index', ['filter'=>'pending_hrd']) }}" class="btn btn-sm {{ $filter==='pending_hrd' ? 'btn-success' : 'btn-outline-success' }}">Menunggu HRD ({{ \App\Models\Leave::where('status_supervisor','approved')->where('status_hrd','pending')->count() }})</a>
    <a href="{{ route('admin.leaves.index', ['filter'=>'approved']) }}" class="btn btn-sm {{ $filter==='approved' ? 'btn-success' : 'btn-outline-success' }}">Disetujui</a>
    <a href="{{ route('admin.leaves.index', ['filter'=>'rejected']) }}" class="btn btn-sm {{ $filter==='rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Ditolak</a>
</div>
@endif

<div class="card">
    <div class="card-header"><h3 class="card-title">
        @if($role==='supervisor') Menunggu Approve Supervisor (Diajukan ke Anda) — hanya yang memilih Anda sebagai Atasan
        @else Semua Pengajuan (HRD Final) @if($filter) — Filter: {{ ucfirst(str_replace('_',' ',$filter)) }} @endif @endif
    </h3>
    <small class="d-block text-muted" style="font-size:11px;">Format atasan: Nama|Role|NIK • Backup: Nama|Role|NIK • 1 departemen yang sama (bukan lintas dept)</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable-leaves" class="table table-sm table-bordered table-striped datatable" style="width:100%">
                <thead><tr><th>Karyawan</th><th>Dept</th><th>Jenis</th><th>Tanggal</th><th>Hari</th><th>Atasan → Backup</th><th>Alasan</th><th>Status SPV</th><th>Status HRD</th><th>Final</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($leaves as $l)
                <tr @if(auth()->user()->role==='supervisor' && $l->supervisor_id==auth()->id()) class="table-warning" @elseif(auth()->user()->role==='hrd' && $l->status_supervisor=='approved' && $l->status_hrd=='pending') class="table-success" @endif>
                    <td>{{ $l->user->name }}<br><small>{{ $l->user->nik }}</small></td>
                    <td>{{ $l->user->employee->department->name ?? '-' }}</td>
                    <td>{{ $l->leaveType->name }}</td>
                    <td><small>{{ $l->start_date }} s/d {{ $l->end_date }}</small></td>
                    <td>{{ $l->total_days }}</td>
                    <td>
                        <small>
                            <strong>{{ $l->supervisor->name ?? '-' }}</strong>|{{ $l->supervisor ? $l->supervisor->display_role : '-' }}|{{ $l->supervisor->nik ?? '-' }}<br>
                            <span class="text-muted">Backup: {{ $l->backupUser->name ?? '-' }}|{{ $l->backupUser ? $l->backupUser->display_role : '-' }}|{{ $l->backupUser->nik ?? '-' }}</span>
                        </small>
                        @if(auth()->user()->role==='supervisor' && $l->supervisor_id==auth()->id()) <span class="badge bg-warning" style="font-size:9px;">Ke Anda</span> @endif
                    </td>
                    <td><small>{{ Str::limit($l->reason,40) }}</small> @if($l->document_path)<a href="{{ asset('uploads/'.$l->document_path) }}" target="_blank" class="badge bg-info">Doc</a>@endif</td>
                    <td>
                        @if($l->status_supervisor=='pending')<span class="badge bg-warning">Pending</span>
                        @elseif($l->status_supervisor=='approved')<span class="badge bg-success">Approved</span>
                        @else<span class="badge bg-danger">Rejected</span>@endif
                        <small class="d-block">{{ $l->supervisor_note }}</small>
                    </td>
                    <td>
                        @if($l->status_hrd=='pending')<span class="badge bg-warning">Pending</span>
                        @elseif($l->status_hrd=='approved')<span class="badge bg-success">Approved</span>
                        @else<span class="badge bg-danger">Rejected</span>@endif
                        <small class="d-block">{{ $l->hrd_note }}</small>
                    </td>
                    <td>
                        @if($l->final_status=='pending')<span class="badge bg-warning">Pending</span>
                        @elseif($l->final_status=='approved')<span class="badge bg-success">Disetujui</span>
                        @else<span class="badge bg-danger">Ditolak</span>@endif
                    </td>
                    <td>
                        @if(auth()->user()->role==='supervisor' && $l->status_supervisor=='pending')
                            <form method="POST" action="{{ route('admin.leaves.approve-supervisor', $l) }}" class="d-inline" onsubmit="return confirm('Approve cuti {{ $l->user->name }}?')">
                                @csrf <input type="hidden" name="action" value="approved">
                                <button class="btn btn-xs btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.leaves.approve-supervisor', $l) }}" class="d-inline" onsubmit="return confirm('Reject cuti {{ $l->user->name }}?')">
                                @csrf <input type="hidden" name="action" value="rejected">
                                <input type="hidden" name="note" value="Ditolak supervisor">
                                <button class="btn btn-xs btn-danger btn-sm">Reject</button>
                            </form>
                        @elseif(auth()->user()->role==='hrd' && $l->status_supervisor=='approved' && $l->status_hrd=='pending')
                            <span class="badge bg-success mb-1 d-block" style="font-size:9px;">Siap Final Approve</span>
                            <form method="POST" action="{{ route('admin.leaves.approve-hrd', $l) }}" class="d-inline" onsubmit="return confirm('Final Approve cuti {{ $l->user->name }}?')">
                                @csrf <input type="hidden" name="action" value="approved">
                                <button class="btn btn-xs btn-success btn-sm">Final Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.leaves.approve-hrd', $l) }}" class="d-inline" onsubmit="return confirm('Final Reject cuti {{ $l->user->name }}?')">
                                @csrf <input type="hidden" name="action" value="rejected">
                                <button class="btn btn-xs btn-danger btn-sm">Final Reject</button>
                            </form>
                        @elseif(auth()->user()->role==='hrd' && $l->status_supervisor=='pending')
                            <small class="text-muted">Menunggu SPV</small>
                        @else
                            <small class="text-muted">-</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center text-muted">Tidak ada pengajuan</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer"><small class="text-muted">Total {{ $leaves->count() }} pengajuan — DataTables pagination aktif (10/25/50/100, cari instan)</small></div>
</div>
@endsection
