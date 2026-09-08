@extends('layouts.admin')
@section('title','Buat Payroll')
@section('header','Generate Payroll Bulanan')
@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.payrolls.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <label>Periode (YYYY-MM)</label>
                    <input type="text" name="period" class="form-control" placeholder="2026-09" value="{{ old('period', date('Y-m')) }}" pattern="\d{4}-\d{2}" required>
                    @error('period')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-4">
                    <label>Cut-off Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-01')) }}" required>
                </div>
                <div class="col-md-4">
                    <label>Cut-off Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', date('Y-m-t')) }}" required>
                </div>
            </div>
            <div class="alert alert-info mt-3">
                <strong>Otomatis:</strong> Sistem hitung dari tabel <code>attendances</code> (hadir/terlambat/alpha/late_minutes/overtime) + <code>leaves</code> approved. Exclude karyawan <code>resigned</code>. Potongan: telat Rp10k/menit, alpha Rp150k/hari, BPJS 1%+2%.
            </div>
            <button class="btn btn-success"><i class="fas fa-cogs"></i> Generate Payroll</button>
            <a href="{{ route('admin.payrolls.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
