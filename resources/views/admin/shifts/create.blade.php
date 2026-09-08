@extends('layouts.admin')
@section('title','Tambah Shift')
@section('header','Tambah Shift')
@section('content')
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
<div class="card" style="max-width:600px;">
    <div class="card-header"><h3 class="card-title">Form Shift Baru</h3></div>
    <form method="POST" action="{{ route('admin.shifts.store') }}">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nama Shift <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Pagi / Siang / Malam" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="row">
                <div class="col-6 form-group">
                    <label>Jam Masuk <span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time','07:00') }}" required>
                    @error('start_time')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-6 form-group">
                    <label>Jam Pulang <span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time','15:00') }}" required>
                    @error('end_time')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="text-muted">Malam: 22:00-06:00</small>
                </div>
            </div>
            <div class="form-group">
                <label>Toleransi Terlambat (menit) <span class="text-danger">*</span></label>
                <input type="number" name="tolerance_late" class="form-control @error('tolerance_late') is-invalid @enderror" value="{{ old('tolerance_late',15) }}" min="0" max="120" required>
                @error('tolerance_late')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <small class="text-muted">Lewat jam+ toleransi = status Terlambat, hitung late_minutes</small>
            </div>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="is_overnight" name="is_overnight" value="1" {{ old('is_overnight')?'checked':'' }}>
                    <label class="custom-control-label" for="is_overnight">Is Overnight (Cross Day, mis. Malam 22:00-06:00)</label>
                </div>
                <small class="text-muted">Centang jika shift lewat tengah malam</small>
            </div>
            <div class="form-group">
                <label>Warna Badge</label>
                <input type="color" name="color" class="form-control @error('color') is-invalid @enderror" value="{{ old('color','#0d6efd') }}" style="height:38px;">
                @error('color')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection
