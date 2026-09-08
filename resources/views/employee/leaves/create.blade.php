@extends('layouts.employee')
@section('title','Ajukan Cuti')
@section('content')
<h5 class="mb-3">Ajukan Cuti/Izin/Sakit</h5>

@if($errors->any())
<div class="alert alert-danger">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

<form method="POST" action="{{ route('employee.leaves.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card card-rounded p-3 mb-3">
        <div class="mb-3">
            <label class="form-label">Jenis <span class="text-danger">*</span></label>
            <select name="leave_type_id" class="form-select" required>
                @foreach($types as $t)
                <option value="{{ $t->id }}" {{ old('leave_type_id')==$t->id?'selected':'' }}>{{ $t->name }} @if($t->quota_days) (Kuota {{ $t->quota_days }} hari) @endif @if($t->requires_document) - Wajib surat dokter @endif</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Atasan (Supervisor) <span class="text-danger">*</span></label>
            <select name="supervisor_id" class="form-select" required>
                <option value="">-- Pilih Atasan --</option>
                @foreach($supervisors as $s)
                <option value="{{ $s->id }}" {{ old('supervisor_id')==$s->id?'selected':'' }}>{{ $s->name }}|{{ ucfirst($s->role) }}|{{ $s->nik ?? '-' }}@if($s->employee?->department) - {{ $s->employee->department->name }}@endif</option>
                @endforeach
            </select>
            <small class="text-muted" style="font-size:11px;">Hanya atasan 1 departemen yang sama yang tampil. Pilih atasan yang akan approve level 1.</small>
            @if($supervisors->isEmpty())
            <div class="alert alert-warning py-1 px-2 mt-1" style="font-size:11px;">Tidak ada atasan di departemen Anda. Hubungi HRD untuk setting supervisor di departemen yang sama.</div>
            @endif
        </div>
        <div class="mb-3">
            <label class="form-label">Backup oleh</label>
            <select name="backup_user_id" class="form-select">
                <option value="">-- Tidak ada / Pilih Backup --</option>
                @foreach($backups as $b)
                <option value="{{ $b->id }}" {{ old('backup_user_id')==$b->id?'selected':'' }}>{{ $b->name }}|{{ ucfirst($b->role) }}|{{ $b->nik ?? '-' }}@if($b->employee?->department) - {{ $b->employee->department->name }}@endif</option>
                @endforeach
            </select>
            <small class="text-muted" style="font-size:11px;">Hanya rekan 1 departemen yang sama yang tampil sebagai backup tugas selama cuti.</small>
            @if($backups->isEmpty())
            <div class="alert alert-warning py-1 px-2 mt-1" style="font-size:11px;">Tidak ada rekan di departemen Anda yang bisa jadi backup.</div>
            @endif
        </div>
        <div class="row">
            <div class="col-6 mb-3">
                <label class="form-label">Mulai <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
            </div>
            <div class="col-6 mb-3">
                <label class="form-label">Selesai <span class="text-danger">*</span></label>
                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Alasan <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Dokumen (Sakit wajib pdf/jpg)</label>
            <input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
        </div>
        <button class="btn btn-primary w-100">Kirim Pengajuan ke Atasan</button>
        <small class="text-muted d-block text-center mt-2" style="font-size:11px;">Akan dilihat supervisor yang kamu pilih di halaman <code>Admin → Pengajuan Cuti</code></small>
    </div>
</form>
<a href="{{ route('employee.leaves.index') }}" class="btn btn-outline-secondary w-100">Kembali</a>
@endsection
