@extends('layouts.admin')
@section('title','Pengajuan Cuti')
@section('header','Pengajuan Cuti / Izin / Sakit')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header"><h3 class="card-title">
        @if(auth()->user()->role==='supervisor') Menunggu Approve Supervisor (Diajukan ke Anda) — hanya yang memilih Anda sebagai Atasan
        @else Semua Pengajuan (HRD Final) @endif
    </h3>
    <small class="d-block text-muted" style="font-size:11px;">Format atasan: Nama|Role|NIK • Backup: Nama|Role|NIK</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead><tr><th>Karyawan</th><th>Dept</th><th>Jenis</th><th>Tanggal</th><th>Hari</th><th>Atasan → Backup</th><th>Alasan</th><th>Status SPV</th><th>Status HRD</th><th>Final</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($leaves as $l)
                <tr @if(auth()->user()->role==='supervisor' && $l->supervisor_id==auth()->id()) class="table-warning" @endif>
                    <td>{{ $l->user->name }}<br><small>{{ $l->user->nik }}</small></td>
                    <td>{{ $l->user->employee->department->name ?? '-' }}</td>
                    <td>{{ $l->leaveType->name }}</td>
                    <td><small>{{ $l->start_date }} s/d {{ $l->end_date }}</small></td>
                    <td>{{ $l->total_days }}</td>
                    <td>
                        <small>
                            <strong>{{ $l->supervisor->name ?? '-' }}</strong>|{{ $l->supervisor ? ucfirst($l->supervisor->role) : '-' }}|{{ $l->supervisor->nik ?? '-' }}<br>
                            <span class="text-muted">Backup: {{ $l->backupUser->name ?? '-' }}|{{ $l->backupUser ? ucfirst($l->backupUser->role) : '-' }}|{{ $l->backupUser->nik ?? '-' }}</span>
                        </small>
                        @if(auth()->user()->role==='supervisor' && $l->supervisor_id==auth()->id()) <span class="badge bg-warning" style="font-size:9px;">Ke Anda</span> @endif
                    </td>
                    <td><small>{{ Str::limit($l->reason,40) }}</small> @if($l->document_path)<a href="{{ asset('storage/'.$l->document_path) }}" target="_blank" class="badge bg-info">Doc</a>@endif</td>
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
                            <form method="POST" action="{{ route('admin.leaves.approve-supervisor', $l) }}" class="d-inline">
                                @csrf <input type="hidden" name="action" value="approved">
                                <button class="btn btn-xs btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.leaves.approve-supervisor', $l) }}" class="d-inline">
                                @csrf <input type="hidden" name="action" value="rejected">
                                <button class="btn btn-xs btn-danger btn-sm">Reject</button>
                            </form>
                        @elseif(auth()->user()->role==='hrd' && $l->status_supervisor=='approved' && $l->status_hrd=='pending')
                            <form method="POST" action="{{ route('admin.leaves.approve-hrd', $l) }}" class="d-inline">
                                @csrf <input type="hidden" name="action" value="approved">
                                <button class="btn btn-xs btn-success btn-sm">Final Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.leaves.approve-hrd', $l) }}" class="d-inline">
                                @csrf <input type="hidden" name="action" value="rejected">
                                <button class="btn btn-xs btn-danger btn-sm">Final Reject</button>
                            </form>
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
    <div class="card-footer">{{ $leaves->links() }}</div>
</div>
@endsection
