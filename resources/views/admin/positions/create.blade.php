@extends('layouts.admin')
@section('title','Tambah Jabatan')
@section('header','Tambah Jabatan')
@section('content')
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
<div class="card" style="max-width:600px;">
    <div class="card-header"><h3 class="card-title">Form Jabatan Baru</h3></div>
    <form method="POST" action="{{ route('admin.positions.store') }}">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Departemen <span class="text-danger">*</span></label>
                <select name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('department_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Nama Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Staff IT, Supervisor Produksi" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Gaji Pokok Default <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                    <input type="number" name="basic_salary_default" class="form-control @error('basic_salary_default') is-invalid @enderror" value="{{ old('basic_salary_default',5000000) }}" min="0" step="1000" required>
                </div>
                @error('basic_salary_default')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                <small class="text-muted">Dipakai saat generate payroll otomatis</small>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.positions.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection
