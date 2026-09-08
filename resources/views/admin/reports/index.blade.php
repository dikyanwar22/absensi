@extends('layouts.admin')
@section('title','Laporan')
@section('header','Laporan & Export')
@section('content')
<div class="card">
    <div class="card-header p-0">
        <ul class="nav nav-pills ml-2 mt-2">
            <li class="nav-item"><a class="nav-link {{ $tab=='attendance'?'active':'' }}" href="{{ route('admin.reports.index',['tab'=>'attendance','start_date'=>$start,'end_date'=>$end,'department_id'=>$departmentId]) }}"><i class="fas fa-calendar-check"></i> Absensi</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab=='payroll'?'active':'' }}" href="{{ route('admin.reports.index',['tab'=>'payroll','payroll_id'=>$payrollId,'department_id'=>$departmentId,'start_date'=>$start,'end_date'=>$end]) }}"><i class="fas fa-money-bill"></i> Gaji</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab=='leave'?'active':'' }}" href="{{ route('admin.reports.index',['tab'=>'leave','department_id'=>$departmentId]) }}"><i class="fas fa-envelope"></i> Cuti</a></li>
        </ul>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-body">
        <form method="GET" class="form-inline flex-wrap">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if(in_array($tab,['attendance','payroll']))
                <div class="form-group mr-2 mb-2">
                    <label class="mr-1 small">Dari</label><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $start }}">
                </div>
                <div class="form-group mr-2 mb-2">
                    <label class="mr-1 small">Sampai</label><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $end }}">
                </div>
            @endif
            <div class="form-group mr-2 mb-2">
                <label class="mr-1 small">Dept</label>
                <select name="department_id" class="form-control form-control-sm">
                    <option value="">-- Semua --</option>
                    @foreach($departments as $d)<option value="{{ $d->id }}" {{ (string)$departmentId===(string)$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            @if($tab=='payroll')
                <div class="form-group mr-2 mb-2">
                    <label class="mr-1 small">Periode Gaji</label>
                    <select name="payroll_id" class="form-control form-control-sm">
                        <option value="">-- Semua Periode --</option>
                        @foreach($payrolls as $p)<option value="{{ $p->id }}" {{ (string)$payrollId===(string)$p->id?'selected':'' }}>{{ $p->period }} ({{ $p->status }})</option>@endforeach
                    </select>
                </div>
            @endif
            <button class="btn btn-primary btn-sm mb-2 mr-1"><i class="fas fa-filter"></i> Filter</button>
            @if($tab=='attendance')
                <a href="{{ route('admin.reports.export-attendance', request()->query()) }}" class="btn btn-success btn-sm mb-2"><i class="fas fa-file-excel"></i> Export Excel</a>
            @elseif($tab=='payroll')
                <a href="{{ route('admin.reports.export-payroll', request()->query()) }}" class="btn btn-success btn-sm mb-2"><i class="fas fa-file-excel"></i> Export Excel</a>
            @else
                <a href="{{ route('admin.reports.export-leave', request()->query()) }}" class="btn btn-success btn-sm mb-2"><i class="fas fa-file-excel"></i> Export Excel</a>
            @endif
        </form>
        <small class="text-muted"><i class="fas fa-info-circle"></i>
            @if($tab=='attendance') Rekap per karyawan: hadir / terlambat / alpha (hari kerja - hadir - cuti approved) / jam lembur @endif
            @if($tab=='payroll') Rekap per detail payroll: gross/potongan/net, filter periode atau rentang tanggal @endif
            @if($tab=='leave') Rekap sisa & terpakai cuti tahunan @endif
        </small>
    </div>
</div>

@if($tab=='attendance')
<div class="card">
    <div class="card-header"><h3 class="card-title">Rekap Absensi Bulanan ({{ $start }} s/d {{ $end }}) - {{ $attendanceRows->count() }} karyawan</h3></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light"><tr><th>#</th><th>Karyawan</th><th>Dept/Jabatan</th><th class="text-center">Hadir</th><th class="text-center">Telat</th><th class="text-center">Alpha*</th><th class="text-center">Telat (m)</th><th class="text-center">Lembur (jam)</th></tr></thead>
                <tbody>
                @forelse($attendanceRows as $i=>$r)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><strong>{{ $r['name'] }}</strong><br><small class="text-muted">{{ $r['nik'] }}</small></td>
                        <td><span class="badge bg-primary">{{ $r['dept'] }}</span><br><small>{{ $r['position'] }}</small></td>
                        <td class="text-center"><span class="badge bg-success">{{ $r['hadir'] }}</span></td>
                        <td class="text-center"><span class="badge bg-warning">{{ $r['terlambat'] }}</span></td>
                        <td class="text-center"><span class="badge bg-danger">{{ $r['alpha'] }}</span></td>
                        <td class="text-center">{{ $r['late_minutes'] }}</td>
                        <td class="text-center">{{ number_format($r['overtime'],2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data. Ubah filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer"><small class="text-muted">* Alpha = hari kerja - hadir - cuti approved (weekend tidak difilter, sesuai logic PayrollController:52)</small></div>
</div>
@endif

@if($tab=='payroll')
<div class="card">
    <div class="card-header"><h3 class="card-title">Rekap Gaji ({{ $payrollRows->count() }} baris)</h3></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light"><tr><th>#</th><th>Periode</th><th>Karyawan</th><th>Dept</th><th class="text-right">Pokok</th><th class="text-right">Tunjangan</th><th class="text-right">Lembur</th><th class="text-right">Gross</th><th class="text-right">Potongan</th><th class="text-right">Bersih</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($payrollRows as $i=>$r)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><span class="badge bg-info">{{ $r['period'] }}</span></td>
                        <td><strong>{{ $r['name'] }}</strong><br><small class="text-muted">{{ $r['nik'] }}</small></td>
                        <td><small>{{ $r['dept'] }}</small></td>
                        <td class="text-right">Rp {{ number_format($r['basic'],0,',','.') }}</td>
                        <td class="text-right">Rp {{ number_format($r['allowances'],0,',','.') }}</td>
                        <td class="text-right">Rp {{ number_format($r['overtime'],0,',','.') }}</td>
                        <td class="text-right"><strong>Rp {{ number_format($r['gross'],0,',','.') }}</strong></td>
                        <td class="text-right text-danger">Rp {{ number_format($r['deduction'],0,',','.') }}</td>
                        <td class="text-right text-success"><strong>Rp {{ number_format($r['net'],0,',','.') }}</strong></td>
                        <td><span class="badge {{ $r['status']=='locked'?'bg-success':'bg-warning' }}">{{ $r['status'] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data payroll. Buat periode di <a href="{{ route('admin.payrolls.index') }}">Payroll</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($tab=='leave')
<div class="card">
    <div class="card-header"><h3 class="card-title">Rekap Cuti ({{ $leaveRows->count() }} karyawan)</h3></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light"><tr><th>#</th><th>Karyawan</th><th>Dept</th><th class="text-center">Terpakai</th><th class="text-center">Sisa</th><th class="text-center">Total Pengajuan</th><th class="text-center">Disetujui</th><th class="text-center">Pending</th><th class="text-center">Ditolak</th></tr></thead>
                <tbody>
                @forelse($leaveRows as $i=>$r)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><strong>{{ $r['name'] }}</strong><br><small class="text-muted">{{ $r['nik'] }}</small></td>
                        <td><span class="badge bg-primary">{{ $r['dept'] }}</span></td>
                        <td class="text-center"><span class="badge bg-warning">{{ $r['used'] }}</span></td>
                        <td class="text-center"><span class="badge bg-success">{{ $r['remaining'] }}</span></td>
                        <td class="text-center">{{ $r['total'] }}</td>
                        <td class="text-center"><span class="badge bg-success">{{ $r['approved'] }}</span></td>
                        <td class="text-center"><span class="badge bg-warning">{{ $r['pending'] }}</span></td>
                        <td class="text-center"><span class="badge bg-danger">{{ $r['rejected'] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
