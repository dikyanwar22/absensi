@extends('layouts.admin')
@section('title','Edit Shift')
@section('header','Edit Shift: '.$shift->name)
@section('content')
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
<div class="card" style="max-width:600px;">
    <div class="card-header"><h3 class="card-title">Edit - {{ $shift->name }}</h3></div>
    <form method="POST" action="{{ route('admin.shifts.update',$shift) }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nama Shift <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$shift->name) }}" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="row">
                <div class="col-6 form-group">
                    <label>Jam Masuk <span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time', \Carbon\Carbon::parse($shift->start_time)->format('H:i')) }}" required>
                </div>
                <div class="col-6 form-group">
                    <label>Jam Pulang <span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time', \Carbon\Carbon::parse($shift->end_time)->format('H:i')) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label>Toleransi Terlambat (menit)</label>
                <input type="number" name="tolerance_late" class="form-control" value="{{ old('tolerance_late',$shift->tolerance_late) }}" min="0" max="120" required>
            </div>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="is_overnight" name="is_overnight" value="1" {{ old('is_overnight',$shift->is_overnight)?'checked':'' }}>
                    <label class="custom-control-label" for="is_overnight">Is Overnight (Cross Day)</label>
                </div>
            </div>
            <div class="form-group">
                <label>Warna Badge</label>
                <input type="color" name="color" class="form-control" value="{{ old('color',$shift->color) }}" style="height:38px;">
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
        </div>
    </form>
</div>
@endsection
