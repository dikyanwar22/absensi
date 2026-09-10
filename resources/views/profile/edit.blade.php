@extends('layouts.employee')
@section('title','Ganti Password')
@section('content')
<h5 class="mb-1"><i class="bi bi-key me-2 text-primary"></i>Ganti Password</h5>
<p class="text-muted small mb-3">Pastikan password panjang & acak agar aman. Staff tidak dapat menghapus akun.</p>

@if(session('status') === 'profile-updated')
    <div class="alert alert-success py-2 small">Profil diperbarui.</div>
@endif
@if(session('status') === 'password-updated')
    <div class="alert alert-success py-2 small">Password berhasil diperbarui!</div>
@endif
@if(session('status') === 'verification-link-sent')
    <div class="alert alert-info py-2 small">Link verifikasi baru telah dikirim ke email.</div>
@endif

{{-- Info Akun Ringkas --}}
<div class="card card-rounded p-3 mb-3 d-flex flex-row align-items-center gap-3">
    @php
        $photo = null;
        try {
            $emp = auth()->user()->employee ?? null;
            if($emp && $emp->photo && file_exists(public_path('uploads/'.$emp->photo))){
                $photo = asset('uploads/'.$emp->photo);
            }
        } catch(\Throwable $e){}
        $avatar = $photo ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=0d6efd&color=fff&size=48';
    @endphp
    <img src="{{ $avatar }}" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
    <div class="flex-grow-1">
        <div class="fw-semibold small">{{ auth()->user()->name }}</div>
        <small class="text-muted">{{ auth()->user()->email }}<br>{{ auth()->user()->nik ?? '-' }} <br>{{ auth()->user()->display_role }}</small>
    </div>
    <a href="{{ route('employee.profile') }}" class="btn btn-sm btn-outline-primary">Profile</a>
</div>

{{-- Form Ganti Password - Mobile Card --}}
<div class="card card-rounded p-3 mb-3">
    <h6 class="mb-2"><i class="bi bi-shield-lock me-1"></i> Update Password</h6>
    <p class="small text-muted mb-3">Buat password bebas tanpa batasan panjang.</p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label small mb-1">Password Saat Ini <span class="text-danger">*</span></label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control form-control-sm" autocomplete="current-password" required placeholder="••••••••">
            @if($errors->updatePassword->has('current_password'))
                <small class="text-danger">{{ $errors->updatePassword->first('current_password') }}</small>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label small mb-1">Password Baru <span class="text-danger">*</span></label>
            <input id="update_password_password" name="password" type="password" class="form-control form-control-sm" autocomplete="new-password" required placeholder="Password baru">
            @if($errors->updatePassword->has('password'))
                <small class="text-danger">{{ $errors->updatePassword->first('password') }}</small>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label small mb-1">Konfirmasi Password Baru <span class="text-danger">*</span></label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control form-control-sm" autocomplete="new-password" required placeholder="Ulangi password baru">
            @if($errors->updatePassword->has('password_confirmation'))
                <small class="text-danger">{{ $errors->updatePassword->first('password_confirmation') }}</small>
            @endif
        </div>

        <button class="btn btn-primary w-100 btn-sm"><i class="bi bi-check-lg me-1"></i> Simpan Password</button>
    </form>
</div>

{{-- Info Biodata Link --}}
<div class="card card-rounded p-3 mb-3">
    <h6 class="small fw-semibold mb-2"><i class="bi bi-person-lines-fill me-1"></i> Update Biodata & Avatar</h6>
    <p class="small text-muted mb-2">Untuk ganti nama, email, NIK, foto, alamat, dll gunakan menu Profile karyawan (smartphone).</p>
    <a href="{{ route('employee.profile') }}" class="btn btn-outline-primary w-100 btn-sm"><i class="bi bi-person me-1"></i> Ke Profile Biodata</a>
</div>

<div class="text-center">
    <a href="{{ route('employee.profile') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Kembali ke Profile</a>
</div>
@endsection
