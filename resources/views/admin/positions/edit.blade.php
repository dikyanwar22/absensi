@extends('layouts.admin')
@section('title','Edit Jabatan')
@section('header','Edit Jabatan: '.$position->name)
@section('content')
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
<div class="card" style="max-width:600px;">
    <div class="card-header"><h3 class="card-title">Edit - {{ $position->name }}</h3></div>
    <form method="POST" action="{{ route('admin.positions.update',$position) }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Departemen <span class="text-danger">*</span></label>
                <select name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ old('department_id',$position->department_id)==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('department_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Nama Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$position->name) }}" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Gaji Pokok Default <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                    <input type="number" name="basic_salary_default" class="form-control @error('basic_salary_default') is-invalid @enderror" value="{{ old('basic_salary_default',$position->basic_salary_default) }}" min="0" step="1000" required>
                </div>
                @error('basic_salary_default')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.positions.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
        </div>
    </form>
</div>
@endsection
