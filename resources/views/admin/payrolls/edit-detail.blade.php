@extends('layouts.admin')
@section('title','Edit Gaji')
@section('header','Edit Rupiah — '.$detail->user->name.' ('.$detail->payroll->period.')')
@section('content')
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Edit Total Rupiah (HRD) — Status: <span class="badge bg-warning">{{ $detail->payroll->status }}</span></h3>
        <a href="{{ route('admin.payrolls.show',$detail->payroll) }}" class="btn btn-default btn-sm ml-auto"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <form method="POST" action="{{ route('admin.payrolls.update-detail',$detail) }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="alert alert-info small">Rumus: <code>Gross = Pokok + Tunjangan + Lembur + Bonus + THR</code> | <code>Net = Gross - Total Potongan (Sistem + Dinamis)</code> — Auto hitung saat save & update <code>payrolls.total_amount</code>. Potongan dinamis (Serikat, Liburan, Ganti Rugi) bisa dihapus (isi 0) atau di-override per karyawan untuk handle salah input/komplain.</div>
            <div class="row">
                <div class="col-md-4"><strong>Karyawan:</strong> {{ $detail->user->name }}<br><small class="text-muted">{{ $detail->user->nik }} • {{ $detail->user->employee->department->name ?? '-' }}</small></div>
                <div class="col-md-4"><strong>Periode:</strong> {{ $detail->payroll->period }} ({{ $detail->payroll->start_date }} - {{ $detail->payroll->end_date }})</div>
                <div class="col-md-4"><strong>Ringkasan Absen:</strong> Hadir {{ $detail->attendance_summary['hadir'] ?? 0 }} | Telat {{ $detail->attendance_summary['terlambat'] ?? 0 }} ({{ $detail->attendance_summary['late_minutes'] ?? 0 }}m) | Alpha {{ $detail->attendance_summary['alpha'] ?? 0 }}</div>
            </div>
            <hr>
            <h6>Pendapatan</h6>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Gaji Pokok <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="basic_salary" class="form-control" value="{{ old('basic_salary',$detail->basic_salary) }}" min="0" step="1000" required></div>
                </div>
                <div class="col-md-2 form-group"><label>Tunj Makan</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="allow_makan" class="form-control" value="{{ old('allow_makan',$detail->allowances['makan'] ?? 0) }}" min="0" step="1000" required></div></div>
                <div class="col-md-2 form-group"><label>Tunj Transport</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="allow_transport" class="form-control" value="{{ old('allow_transport',$detail->allowances['transport'] ?? 0) }}" min="0" step="1000" required></div></div>
                <div class="col-md-2 form-group"><label>Tunj Jabatan</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="allow_jabatan" class="form-control" value="{{ old('allow_jabatan',$detail->allowances['jabatan'] ?? 0) }}" min="0" step="1000" required></div></div>
                <div class="col-md-2 form-group"><label>Lembur</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="overtime_pay" class="form-control" value="{{ old('overtime_pay',$detail->overtime_pay) }}" min="0" step="1000" required></div></div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group"><label>Bonus</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="bonus" class="form-control" value="{{ old('bonus',$detail->bonus) }}" min="0" step="1000" required></div></div>
                <div class="col-md-4 form-group"><label>THR</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="thr" class="form-control" value="{{ old('thr',$detail->thr) }}" min="0" step="1000" required></div></div>
                <div class="col-md-4 form-group"><label>Catatan (alasan komplain/koreksi)</label><input type="text" name="notes" class="form-control form-control-sm" value="{{ old('notes',$detail->notes) }}" placeholder="Opsional: koreksi kelebihan potong"></div>
            </div>
            <hr>
            <h6>Potongan Sistem (Otomatis)</h6>
            <div class="row">
                <div class="col-md-3 form-group"><label>Terlambat</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="ded_terlambat" class="form-control" value="{{ old('ded_terlambat',$detail->deductions['terlambat'] ?? 0) }}" min="0" step="1000" required></div></div>
                <div class="col-md-3 form-group"><label>Alpha</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="ded_alpha" class="form-control" value="{{ old('ded_alpha',$detail->deductions['alpha'] ?? 0) }}" min="0" step="1000" required></div></div>
                <div class="col-md-3 form-group"><label>BPJS Kes 1%</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="ded_bpjs_kes" class="form-control" value="{{ old('ded_bpjs_kes',$detail->deductions['bpjs_kes'] ?? 0) }}" min="0" step="1000" required></div></div>
                <div class="col-md-3 form-group"><label>BPJS TK 2%</label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="ded_bpjs_tk" class="form-control" value="{{ old('ded_bpjs_tk',$detail->deductions['bpjs_tk'] ?? 0) }}" min="0" step="1000" required></div></div>
            </div>
            <hr>
            <h6>Potongan Dinamis (Input HRD — Serikat, Liburan, Ganti Rugi, dll) <small class="text-muted">— isi 0 untuk hapus potongan ini pada karyawan ini (komplain)</small></h6>
            @php
                $systemKeys = ['terlambat','alpha','bpjs_kes','bpjs_tk','lain'];
                $dynamicDeductions = collect($detail->deductions ?? [])->reject(fn($v,$k) => in_array($k, $systemKeys));
            @endphp
            @if($dynamicDeductions->isEmpty())
                <div class="alert alert-secondary small">Tidak ada potongan dinamis untuk karyawan ini. Anda bisa tambah baru di bawah.</div>
            @else
                <div class="row">
                    @foreach($dynamicDeductions as $name => $amount)
                    <div class="col-md-4 form-group">
                        <label>{{ $name }}</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="number" name="deductions_dynamic[{{ $name }}]" class="form-control" value="{{ $amount }}" min="0" step="1000" placeholder="0 = hapus">
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
            <div class="border p-2 rounded bg-light">
                <strong>Tambah Potongan Insidentil khusus karyawan ini (opsional):</strong>
                <small class="text-muted">Misal: karyawan ini saja yang kena Ganti Rugi, karyawan lain tidak.</small>
                <div id="dynamic-add-rows">
                    <div class="row mt-2">
                        <div class="col-md-6"><input type="text" name="deduction_names[]" class="form-control form-control-sm" placeholder="Nama potongan baru (ex: Ganti Rugi Laptop)"></div>
                        <div class="col-md-4"><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="deduction_amounts[]" class="form-control" min="0" step="1000" placeholder="0"></div></div>
                        <div class="col-md-2"><button type="button" class="btn btn-sm btn-success" onclick="addDeductionRow()"><i class="fas fa-plus"></i></button></div>
                    </div>
                </div>
                <small class="text-muted">Kosongkan jika tidak perlu. Jika ingin tambah potongan global untuk semua karyawan, gunakan menu Master Potongan.</small>
            </div>
            <div class="alert alert-secondary small mt-3">
                Saat ini: Gross Rp {{ number_format($detail->gross_salary,0,',','.') }} | Potongan Rp {{ number_format($detail->total_deduction,0,',','.') }} | <strong>Net Rp {{ number_format($detail->net_salary,0,',','.') }}</strong> — akan dihitung ulang otomatis.
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.payrolls.show',$detail->payroll) }}" class="btn btn-default"><i class="fas fa-times"></i> Batal</a>
            <button class="btn btn-warning"><i class="fas fa-save"></i> Simpan & Hitung Ulang</button>
        </div>
    </form>
</div>
@push('scripts')
<script>
function addDeductionRow(){
    const container = document.getElementById('dynamic-add-rows');
    const row = document.createElement('div');
    row.className = 'row mt-2';
    row.innerHTML = `<div class="col-md-6"><input type="text" name="deduction_names[]" class="form-control form-control-sm" placeholder="Nama potongan"></div><div class="col-md-4"><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="deduction_amounts[]" class="form-control" min="0" step="1000" placeholder="0"></div></div><div class="col-md-2"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.row').remove()"><i class="fas fa-trash"></i></button></div>`;
    container.appendChild(row);
}
</script>
@endpush
@endsection
