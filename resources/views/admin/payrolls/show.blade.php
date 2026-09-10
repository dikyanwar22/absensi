@extends('layouts.admin')
@section('title','Detail Payroll')
@section('header',"Payroll {$payroll->period} - {$payroll->status}")
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <div>
            <strong>Periode:</strong> {{ $payroll->period }} ({{ $payroll->start_date }} - {{ $payroll->end_date }})<br>
            <small>Status: <span class="badge bg-{{ $payroll->status=='draft'?'warning':'success' }}">{{ $payroll->status }}</span> | {{ $payroll->total_employees }} karyawan | Total: Rp {{ number_format($payroll->total_amount,0,',','.') }}</small>
        </div>
        <div>
            @if($payroll->status=='draft')
            <form method="POST" action="{{ route('admin.payrolls.lock', $payroll) }}" class="d-inline">
                @csrf <button class="btn btn-warning btn-sm" onclick="return confirm('Kunci periode? Setelah dikunci slip bisa dilihat karyawan')"><i class="fas fa-lock"></i> Kunci Periode</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.payrolls.unlock', $payroll) }}" class="d-inline">
                @csrf <button class="btn btn-info btn-sm" onclick="return confirm('Unlock? Periode akan kembali ke Draft agar bisa di-edit, lalu Kunci lagi. Karyawan tidak lihat slip saat Unlock? Tetap fleksibel.')"><i class="fas fa-unlock"></i> Unlock ke Draft</button>
            </form>
            @endif
            <a href="{{ route('admin.payrolls.slip-massal', $payroll) }}" target="_blank" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> Slip Massal PDF</a>
            <a href="{{ route('admin.payrolls.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
    @if($payroll->status=='draft')
    <div class="alert alert-info small mb-0"><i class="fas fa-info-circle"></i> Mode <strong>Draft</strong> — HRD bisa <strong>Edit Rupiah</strong> (pokok, tunjangan, bonus, potongan dinamis) sebelum Kunci. Potongan dinamis (Serikat, Liburan, Ganti Rugi) muncul per baris di slip. Salah input/komplain → Edit per karyawan (isi 0 untuk hapus potongan karyawan itu). Fleksibel: Locked → Unlock → Edit → Lock lagi.</div>
    @else
    <div class="alert alert-warning small mb-0"><i class="fas fa-unlock"></i> Periode <strong>Locked</strong> — klik <strong>Unlock ke Draft</strong> untuk edit lagi, lalu <strong>Kunci</strong> kembali. Slip tetap bisa dilihat karyawan saat Locked. Rincian potongan dinamis tetap tersimpan di slip.</div>
    @endif
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable-payroll-details" class="table table-sm table-bordered table-striped datatable" style="width:100%">
<<<<<<< HEAD
                <thead><tr><th>NIK</th><th>Nama</th><th>Dept</th><th>Hadir</th><th>Telat</th><th>Alpha</th><th>Pokok</th><th>Tunjangan</th><th>Lembur+Bonus</th><th>Potongan (Dinamis)</th><th>Bersih</th><th>Slip</th><th>Edit</th></tr></thead>
=======
                <thead><tr><th>NIK</th><th>Nama</th><th>Dept</th><th>Hadir</th><th>Telat</th><th>Alpha</th><th>Pokok</th><th>Tunjangan</th><th>Lembur+Bonus</th><th>Potongan</th><th>Bersih</th><th>Slip</th><th>Edit</th></tr></thead>
>>>>>>> 03b750586559a20aacd64af62893c95988533e04
                <tbody>
                @foreach($payroll->details as $d)
                <tr>
                    <td><small>{{ $d->user->nik }}</small></td>
                    <td>{{ $d->user->name }}</td>
                    <td><small>{{ $d->user->employee->department->name ?? '-' }}</small></td>
                    <td class="text-center">{{ $d->attendance_summary['hadir'] ?? 0 }}</td>
                    <td class="text-center">{{ $d->attendance_summary['terlambat'] ?? 0 }} ({{ $d->attendance_summary['late_minutes'] ?? 0 }}m)</td>
                    <td class="text-center">{{ $d->attendance_summary['alpha'] ?? 0 }}</td>
                    <td><small>Rp {{ number_format($d->basic_salary,0,',','.') }}</small></td>
                    <td><small>Rp {{ number_format(array_sum($d->allowances ?? []),0,',','.') }}</small></td>
                    <td><small>Rp {{ number_format($d->overtime_pay + $d->bonus + $d->thr,0,',','.') }}</small></td>
                    <td>
                        <small class="text-danger">Rp {{ number_format($d->total_deduction,0,',','.') }}</small>
                        @php
                            $sys = ['terlambat','alpha','bpjs_kes','bpjs_tk','lain'];
                            $dyn = collect($d->deductions ?? [])->reject(fn($v,$k)=>in_array($k,$sys))->keys();
                        @endphp
                        @if($dyn->isNotEmpty())
                            <br><small class="text-muted">{{ $dyn->implode(', ') }}</small>
                        @endif
                    </td>
                    <td><strong>Rp {{ number_format($d->net_salary,0,',','.') }}</strong></td>
                    <td><a href="{{ route('admin.payrolls.slip', $d) }}" target="_blank" class="btn btn-xs btn-danger btn-sm"><i class="fas fa-print"></i> PDF</a></td>
                    <td>
                        @if($payroll->status=='draft')
                            <a href="{{ route('admin.payrolls.edit-detail', $d) }}" class="btn btn-xs btn-warning btn-sm"><i class="fas fa-edit"></i> Edit Rp</a>
                        @else
                            <span class="badge bg-secondary">Locked</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
