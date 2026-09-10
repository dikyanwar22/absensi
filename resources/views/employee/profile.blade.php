@extends('layouts.employee')
@section('title','Profile')
@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 small">{{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
<div class="alert alert-danger py-2 small">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

@php
    $photoUrl = null;
    if (!empty($employee?->photo) && file_exists(public_path('uploads/'.$employee->photo))) {
        $photoUrl = asset('uploads/'.$employee->photo);
    } else {
        $photoUrl = 'https://ui-avatars.com/api/?name='.urlencode($user->name ?? 'Karyawan').'&background=0d6efd&color=fff&size=120';
    }
    $daysLeftText = null;
    if(isset($contractDaysLeft)){
        if($contractDaysLeft < 0) $daysLeftText = 'Kontrak habis '.abs($contractDaysLeft).' hari lalu';
        elseif($contractDaysLeft == 0) $daysLeftText = 'Habis hari ini';
        else $daysLeftText = 'Sisa '.$contractDaysLeft.' hari';
    }
@endphp

{{-- Avatar Card --}}
<div class="card card-rounded p-4 text-center mb-3">
    <div class="position-relative d-inline-block">
        <img id="avatarPreview" src="{{ $photoUrl }}" class="rounded-circle mx-auto mb-2" width="100" height="100" style="object-fit:cover; border:3px solid #0d6efd;">
        <label for="photoBottom" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px; cursor:pointer; border:2px solid #fff;"><i class="bi bi-camera-fill small"></i></label>
    </div>
    <h5 class="mb-0">{{ $user->name }}</h5>
    <small class="text-muted">NIK: {{ $user->nik ?? '-' }}</small>
    <div class="mt-2">
        <span class="badge bg-primary">{{ $user->display_role }}</span>
        @if($employee)<span class="badge bg-secondary">{{ ucfirst($employee->employment_status ?? '-') }}</span>@endif
    </div>
    <small class="d-block text-muted mt-2" style="font-size:11px;">Klik icon kamera → pilih foto → klik <b>Simpan Perubahan</b> di bawah</small>
    {{-- Form avatar terpisah tetap ada untuk kompatibilitas, tapi hidden --}}
    <form id="avatarForm" action="{{ route('employee.profile.avatar') }}" method="POST" enctype="multipart/form-data" class="d-none">
        @csrf
        <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/jpg,image/webp" class="d-none" onchange="previewAvatar(this);">
    </form>
</div>

{{-- Status Kepegawaian Dinamis --}}
<div class="card card-rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h6 class="mb-0">Status Kepegawaian</h6>
        @if($contractDaysLeft !== null)
            @if($contractDaysLeft <= 7 && $contractDaysLeft >=0)
                <span class="badge bg-danger">{{ $daysLeftText }}</span>
            @elseif($contractDaysLeft <=30)
                <span class="badge bg-warning text-dark">{{ $daysLeftText }}</span>
            @else
                <span class="badge bg-success">{{ $daysLeftText }}</span>
            @endif
        @endif
    </div>
    <div class="small">
        <div class="d-flex justify-content-between"><span class="text-muted">Departemen</span><span class="fw-semibold">{{ $employee?->department?->name ?? '-' }}</span></div>
        <div class="d-flex justify-content-between"><span class="text-muted">Jabatan</span><span class="fw-semibold">{{ $employee?->position?->name ?? '-' }}</span></div>
        <div class="d-flex justify-content-between"><span class="text-muted">Shift</span><span class="fw-semibold">{{ $employee?->shift?->name ?? '-' }} {{ $employee?->shift ? '('.$employee->shift->start_time.'-'.$employee->shift->end_time.')' : '' }}</span></div>
        <div class="d-flex justify-content-between"><span class="text-muted">Tgl Masuk</span><span>{{ $employee?->join_date?->format('d M Y') ?? '-' }}</span></div>
        <div class="d-flex justify-content-between"><span class="text-muted">Akhir Kontrak</span><span>{{ $employee?->contract_end_date?->format('d M Y') ?? '-' }}</span></div>
        <div class="d-flex justify-content-between"><span class="text-muted">Lokasi Kantor</span><span>{{ $employee?->officeLocation?->name ?? '-' }}</span></div>
    </div>
    @if(isset($contractProgress))
    <div class="progress mt-2" style="height:8px;"><div class="progress-bar bg-primary" style="width: {{ $contractProgress }}%"></div></div>
    <small class="text-muted" style="font-size:11px;">Progres kontrak {{ $contractProgress }}%</small>
    @endif
</div>

{{-- Sisa Cuti Dinamis --}}
<div class="card card-rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Sisa Cuti</h6>
        <small class="text-muted">{{ $remainingLeave }} / {{ $leaveQuota }} hari</small>
    </div>
    <div class="progress mt-2" style="height:20px;">
        @php $pct = $leaveQuota>0 ? round($remainingLeave/$leaveQuota*100) : 0; @endphp
        <div class="progress-bar {{ $remainingLeave<=2 ? 'bg-danger' : ($remainingLeave<=5 ? 'bg-warning text-dark' : 'bg-success') }}" style="width: {{ $pct }}%">{{ $remainingLeave }} hari</div>
    </div>
    <small class="text-muted" style="font-size:11px;">Terpakai {{ $usedLeave }} hari tahun {{ date('Y') }}</small>
</div>

{{-- Form Biodata --}}
<div class="card card-rounded p-3 mb-3">
    <h6 class="mb-3"><i class="bi bi-person-lines-fill me-1"></i> Update Biodata</h6>
    <form action="{{ route('employee.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="mb-2">
            <label class="form-label small mb-1">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="mb-2">
            <label class="form-label small mb-1">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="mb-2">
            <label class="form-label small mb-1">NIK</label>
            <input type="text" class="form-control form-control-sm bg-light" value="{{ $user->nik ?? '-' }}" disabled>
            <small class="text-muted" style="font-size:11px;">NIK tidak dapat diubah, hubungi HRD</small>
        </div>
        <div class="mb-2">
            <label class="form-label small mb-1">No. HP / WA</label>
            <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $employee->phone ?? '') }}" placeholder="08xxxxxxxxxx">
        </div>
        <div class="mb-2">
            <label class="form-label small mb-1">Alamat</label>
            <textarea name="address" rows="2" class="form-control form-control-sm" placeholder="Alamat domisili">{{ old('address', $employee->address ?? '') }}</textarea>
        </div>
        <div class="row">
            <div class="col-6 mb-2">
                <label class="form-label small mb-1">Bank</label>
                <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name', $employee->bank_name ?? '') }}" placeholder="BCA / Mandiri">
            </div>
            <div class="col-6 mb-2">
                <label class="form-label small mb-1">No. Rekening</label>
                <input type="text" name="bank_account" class="form-control form-control-sm" value="{{ old('bank_account', $employee->bank_account ?? '') }}" placeholder="1234567890">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label small mb-1">Ganti Avatar (opsional)</label>
            <input type="file" name="photo" id="photoBottom" class="form-control form-control-sm" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewAvatar(this)">
            <small class="text-muted" style="font-size:11px;">Bisa via icon kamera di atas atau input ini → lalu klik Simpan Perubahan</small>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="bi bi-check-lg me-1"></i> Simpan Perubahan</button>
    </form>
</div>

{{-- Slip Gaji Terakhir Dinamis --}}
<div class="card card-rounded p-3 mb-3">
    <h6 class="mb-2">Slip Gaji Terakhir</h6>
    @if($lastPayroll)
        <div class="d-flex justify-content-between align-items-center border rounded p-2">
            <div><div class="fw-semibold">{{ $lastPayroll->payroll->period }}</div><small class="text-muted">Rp {{ number_format($lastPayroll->net_salary,0,',','.') }}</small></div>
            <a href="{{ route('employee.payslip.pdf', $lastPayroll->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> PDF</a>
        </div>
    @else
        <div class="text-center text-muted small py-2">Belum ada slip gaji terkunci</div>
        <a href="{{ route('employee.payslip') }}" class="btn btn-sm btn-outline-secondary w-100 mt-2">Lihat Slip Gaji</a>
    @endif
</div>

{{-- Aksi Lain --}}
<div class="d-grid gap-2 mb-3">
    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-key me-1"></i> Ganti Password</a>
    <button onclick="confirmLogoutEmployee(event)" class="btn btn-danger w-100 btn-sm"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
</div>

@push('js')
<script>
function previewAvatar(input){
    if(input.files && input.files[0]){
        const r = new FileReader();
        r.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        r.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
