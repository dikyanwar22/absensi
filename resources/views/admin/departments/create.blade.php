@extends('layouts.admin')
@section('title','Tambah Departemen')
@section('header','Tambah Departemen')
@section('content')

@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card" style="max-width:600px;">
    <div class="card-header"><h3 class="card-title">Form Departemen Baru</h3></div>
    <form method="POST" action="{{ route('admin.departments.store') }}">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nama Departemen <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: IT, HRD, Produksi, Keuangan" required maxlength="100">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <small class="text-muted">Harus unik</small>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Deskripsi singkat departemen (opsional)">{{ old('description') }}</textarea>
                @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.departments.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection
