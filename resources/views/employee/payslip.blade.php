@extends('layouts.employee')
@section('title','Slip Gaji')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Slip Gaji</h5>
    <span class="badge bg-primary">{{ $payrolls->total() }} periode</span>
</div>

{{-- Filter History by Periode Gajian --}}
<form method="GET" class="card card-rounded p-2 mb-3">
    <div class="d-flex gap-2 align-items-center">
        <div class="flex-grow-1">
            <label class="small text-muted mb-1" style="font-size:11px;">Filter Periode (History)</label>
            <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">— Semua Periode (History) —</option>
                @foreach($periods as $per)
                    <option value="{{ $per }}" {{ $selectedPeriod==$per?'selected':'' }}>{{ $per }} @if($loop->first) • Terbaru @endif</option>
                @endforeach
            </select>
        </div>
        @if($selectedPeriod)
            <a href="{{ route('employee.payslip') }}" class="btn btn-sm btn-outline-secondary align-self-end">Reset</a>
        @endif
    </div>
    @if($periods->isEmpty())
        <small class="text-muted mt-2 d-block" style="font-size:11px;">Belum ada periode terkunci. Minta HRD generate & lock di <code>Admin → Payroll</code>.</small>
    @else
        <small class="text-muted mt-2 d-block" style="font-size:11px;"><i class="bi bi-clock-history me-1"></i>Menampilkan {{ $selectedPeriod ? 'periode '.$selectedPeriod : 'semua history ('.$periods->count().' bulan)' }} — terbaru dulu.</small>
    @endif
</form>

@forelse($payrolls as $p)
<div class="card card-rounded p-3 mb-2">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold"><i class="bi bi-calendar3 me-1 text-primary"></i>{{ $p->payroll->period ?? '-' }} @if($p->payroll && $p->payroll->status=='locked')<span class="badge bg-success" style="font-size:10px;">Terkunci</span>@endif</div>
            <small class="text-muted">Periode: {{ $p->payroll->start_date?->format('d M Y') ?? '-' }} - {{ $p->payroll->end_date?->format('d M Y') ?? '-' }}</small><br>
            <small class="text-muted">Bersih: <strong class="text-success">Rp {{ number_format($p->net_salary,0,',','.') }}</strong></small>
            <br><small class="text-muted" style="font-size:11px;">Pokok: Rp {{ number_format($p->basic_salary,0,',','.') }} | Potongan: Rp {{ number_format($p->total_deduction,0,',','.') }} | Gross: Rp {{ number_format($p->gross_salary,0,',','.') }}</small>
        </div>
        <a href="{{ route('employee.payslip.pdf', $p->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-download me-1"></i> PDF</a>
    </div>
</div>
@empty
<div class="card card-rounded p-4 text-center text-muted">
    @if($selectedPeriod)
        <div class="mb-2"><i class="bi bi-inbox fs-3"></i></div>
        <div>Tidak ada slip untuk periode <strong>{{ $selectedPeriod }}</strong></div>
        <a href="{{ route('employee.payslip') }}" class="btn btn-sm btn-outline-primary mt-3">Lihat Semua History</a>
    @else
        Belum ada slip gaji (hanya periode yang sudah dikunci HRD yang tampil)<br>
        <small style="font-size:11px;">History bulan-bulan sebelumnya akan muncul otomatis setelah HRD lock periode di <code>Admin → Payroll</code>. Coba filter periode di atas setelah ada data.</small>
    @endif
</div>
@endforelse
<div class="mt-3">{{ $payrolls->links() }}</div>

<div class="card card-rounded p-3 mt-3 bg-light border-0">
    <h6 class="small fw-semibold mb-2"><i class="bi bi-lightbulb me-1"></i>Cara coba menu ini</h6>
    <ol class="small text-muted mb-0" style="font-size:11.5px; padding-left:18px;">
        <li>Login HRD → <code>/admin/payrolls</code> → <strong>Buat Periode Baru</strong> (isi YYYY-MM & cut-off)</li>
        <li>Klik <strong>Generate Payroll</strong> → lalu <strong>Kunci Periode</strong> (Locked)</li>
        <li>Kembali login Karyawan → <code>/employee/payslip</code> → history bulan muncul, filter by periode bisa dipakai, PDF bisa didownload</li>
    </ol>
</div>
@endsection
