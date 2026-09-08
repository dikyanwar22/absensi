@extends('layouts.admin')
@section('title','Edit Departemen')
@section('header','Edit Departemen: '.$department->name)
@section('content')

@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card" style="max-width:600px;">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Edit - {{ $department->name }}</h3>
        <small class="text-muted">{{ $department->employees_count }} karyawan • {{ $department->positions_count }} jabatan</small>
    </div>
    <form method="POST" action="{{ route('admin.departments.update', $department) }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nama Departemen <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name) }}" required maxlength="100">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $department->description) }}</textarea>
                @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.departments.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
        </div>
    </form>
</div>
@endsection
