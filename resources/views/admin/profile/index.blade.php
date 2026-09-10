@extends('layouts.admin')
@section('title','Profile')
@section('header','Profile Saya')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

@php
    $photoUrl = null;
    if (!empty($employee?->photo) && file_exists(public_path('uploads/'.$employee->photo))) {
        $photoUrl = asset('uploads/'.$employee->photo);
    } else {
        $photoUrl = 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0d6efd&color=fff&size=120';
    }
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile text-center">
                <div class="position-relative d-inline-block">
                    <img id="avatarPreview" src="{{ $photoUrl }}" class="profile-user-img img-fluid img-circle" style="width:120px;height:120px;object-fit:cover;border:3px solid #0d6efd;">
                    <label for="photoInput" class="position-absolute bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;bottom:5px;right:5px;cursor:pointer;border:2px solid #fff;"><i class="fas fa-camera fa-sm"></i></label>
                </div>
                <h3 class="profile-username mt-2">{{ $user->name }}</h3>
                <p class="text-muted">NIK: {{ $user->nik ?? '-' }}<br>{{ $user->email }}<br><span class="badge bg-primary">{{ $user->role }}</span> @if($employee)<span class="badge bg-secondary">{{ ucfirst($employee->employment_status ?? '-') }}</span>@endif</p>
                <form id="avatarForm" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <input type="file" name="photo" id="photoInput" accept="image/*" class="d-none" onchange="previewAvatar(this); document.getElementById('btnUploadAvatar').classList.remove('d-none');">
                    {{-- hidden keep other values --}}
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="nik" value="{{ $user->nik }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="phone" value="{{ $employee->phone ?? '' }}">
                    <input type="hidden" name="address" value="{{ $employee->address ?? '' }}">
                    <button id="btnUploadAvatar" type="submit" class="btn btn-primary btn-sm d-none"><i class="fas fa-upload"></i> Upload Avatar</button>
                </form>
                <small class="text-muted d-block mt-2">JPG/PNG max 2MB • klik ikon kamera</small>
                <hr>
                <div class="text-left small">
                    <p><strong><i class="fas fa-building mr-1"></i> Dept:</strong> {{ $employee->department->name ?? '-' }}</p>
                    <p><strong><i class="fas fa-briefcase mr-1"></i> Jabatan:</strong> {{ $employee->position->name ?? '-' }}</p>
                    <p><strong><i class="fas fa-clock mr-1"></i> Shift:</strong> {{ $employee->shift->name ?? '-' }}</p>
                    <p><strong><i class="fas fa-calendar mr-1"></i> Join:</strong> {{ $employee->join_date?->format('d M Y') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-edit"></i> Update Biodata</h3></div>
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$user->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>NIK</label>
                            <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik',$user->nik) }}" placeholder="NIK KTP">
                            @error('nik')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email',$user->email) }}" required>
                        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>No. HP</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone',$employee->phone ?? '') }}" placeholder="08xxxxxxxxxx">
                            @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Ganti Avatar (opsional)</label>
                            <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                            @error('photo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="Alamat domisili">{{ old('address',$employee->address ?? '') }}</textarea>
                        @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <hr>
                    <h6><i class="fas fa-key"></i> Ganti Password (opsional)</h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ganti">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <button onclick="confirmLogout()" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
function previewAvatar(input){
    if(input.files && input.files[0]){
        const r=new FileReader();
        r.onload=e=>document.getElementById('avatarPreview').src=e.target.result;
        r.readAsDataURL(input.files[0]);
    }
}
function confirmLogout(){
    Swal.fire({
        title: 'Yakin logout?',
        text: 'Anda akan keluar dari sesi admin',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal'
    }).then((result)=>{
        if(result.isConfirmed){ document.getElementById('logout-form').submit(); }
    });
}
</script>
@endpush
@endsection
