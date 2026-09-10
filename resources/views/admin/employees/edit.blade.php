@extends('layouts.admin')
@section('title','Edit Karyawan')
@section('header','Edit Karyawan: '.$employee->user->name)
@section('content')
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<form method="POST" action="{{ route('admin.employees.update',$employee) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row">
<div class="col-md-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Akun Login</h3></div>
        <div class="card-body">
            <div class="form-group"><label>Nama <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name',$employee->user->name) }}" required></div>
            <div class="form-group"><label>NIK (Nomor Induk Karyawan)</label><input type="text" name="nik" class="form-control" value="{{ old('nik',$employee->user->nik) }}" readonly> </div>
            <div class="form-group"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" value="{{ old('email',$employee->user->email) }}" required></div>
            <div class="form-group"><label>Password (kosongkan jika tidak ganti)</label><input type="password" name="password" class="form-control" placeholder="******"></div>
            <div class="form-group" hidden><label>Role <span class="text-danger">*</span> <small class="text-muted">(dari jabatan)</small></label>
                <input type="text" name="role" id="role" class="form-control" value="{{ old('role',$employee->user->role) }}" placeholder="otomatis: manager_finance" list="roleListEdit">
                <datalist id="roleListEdit">
                    @php $existingRoles = \App\Models\User::distinct()->pluck('role')->filter(); @endphp
                    @foreach($existingRoles as $r)<option value="{{ $r }}">@endforeach
                </datalist>
                <small class="text-muted" style="font-size:11px;">Otomatis dari jabatan (slug manager_finance), bisa diubah manual.</small>
            </div>
            <div class="form-group">
                <label>Foto</label>
                @if($employee->photo && file_exists(public_path('uploads/'.$employee->photo)))
                    <div class="mb-2"><img src="{{ asset('uploads/'.$employee->photo) }}" width="80" height="80" class="rounded-circle" style="object-fit:cover;"></div>
                @endif
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Data Kepegawaian</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 form-group"><label>Departemen <span class="text-danger">*</span></label><select name="department_id" id="department_id" class="form-control" required>@foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id',$employee->department_id)==$d->id?'selected':'' }} data-name="{{ strtolower($d->name) }}">{{ $d->name }}</option>@endforeach</select></div>
                <div class="col-6 form-group"><label>Jabatan <span class="text-danger">*</span></label><select name="position_id" id="position_id" class="form-control" required>@foreach($positions as $p)<option value="{{ $p->id }}" {{ old('position_id',$employee->position_id)==$p->id?'selected':'' }} data-dept="{{ strtolower($p->department->name) }}">{{ $p->name }} - {{ $p->department->name }}</option>@endforeach</select></div>
            </div>
            <div class="row">
                <div class="col-6 form-group"><label>Shift</label><select name="shift_id" class="form-control"><option value="">-- Pilih --</option>@foreach($shifts as $s)<option value="{{ $s->id }}" {{ old('shift_id',$employee->shift_id)==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
                <div class="col-6 form-group"><label>Lokasi</label><select name="office_location_id" class="form-control"><option value="">-- Pilih --</option>@foreach($offices as $o)<option value="{{ $o->id }}" {{ old('office_location_id',$employee->office_location_id)==$o->id?'selected':'' }}>{{ $o->name }}</option>@endforeach</select></div>
            </div>
            <div class="form-group"><label>No HP</label><input type="text" name="phone" class="form-control" value="{{ old('phone',$employee->phone) }}"></div>
            <div class="form-group"><label>Alamat</label><textarea name="address" class="form-control" rows="2">{{ old('address',$employee->address) }}</textarea></div>
            <div class="row">
                <div class="col-6 form-group"><label>Tgl Masuk <span class="text-danger">*</span></label><input type="date" name="join_date" class="form-control" value="{{ old('join_date',$employee->join_date?->format('Y-m-d')) }}" required></div>
                <div class="col-6 form-group"><label>Status <span class="text-danger">*</span></label><select name="employment_status" class="form-control" required><option value="kontrak" {{ $employee->employment_status=='kontrak'?'selected':'' }}>Kontrak</option><option value="tetap" {{ $employee->employment_status=='tetap'?'selected':'' }}>Tetap</option><option value="magang" {{ $employee->employment_status=='magang'?'selected':'' }}>Magang</option><option value="probation" {{ $employee->employment_status=='probation'?'selected':'' }}>Probation</option><option value="resigned" {{ $employee->employment_status=='resigned'?'selected':'' }}>Resigned</option></select></div>
            </div>
            <div class="row">
                <div class="col-6 form-group"><label>Akhir Kontrak</label><input type="date" name="contract_end_date" class="form-control" value="{{ old('contract_end_date',$employee->contract_end_date?->format('Y-m-d')) }}"></div>
                <div class="col-6 form-group"><label>Tgl Resign</label><input type="date" name="resign_date" class="form-control" value="{{ old('resign_date',$employee->resign_date?->format('Y-m-d')) }}"></div>
            </div>
            <div class="row">
                <div class="col-6 form-group"><label>Bank</label><input type="text" name="bank_name" class="form-control" value="{{ old('bank_name',$employee->bank_name) }}"></div>
                <div class="col-6 form-group"><label>No Rekening</label><input type="text" name="bank_account" class="form-control" value="{{ old('bank_account',$employee->bank_account) }}"></div>
            </div>
            <div class="row">
                <div class="col-6 form-group"><label>BPJS Kes</label><input type="text" name="bpjs_kes" class="form-control" value="{{ old('bpjs_kes',$employee->bpjs_kes) }}"></div>
                <div class="col-6 form-group"><label>BPJS TK</label><input type="text" name="bpjs_tk" class="form-control" value="{{ old('bpjs_tk',$employee->bpjs_tk) }}"></div>
            </div>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active',$employee->is_active)?'checked':'' }}>
                    <label class="custom-control-label" for="is_active">Akun Aktif</label>
                </div>
                <small class="text-muted">Nonaktif = tidak bisa login & exclude payroll</small>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
        </div>
    </div>
</div>
</div>
</form>
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const posSelect = document.getElementById('position_id');
    const roleSelect = document.getElementById('role');
    if(!posSelect || !roleSelect) return;
    function slugify(str){
        return str.toLowerCase().trim().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'');
    }
    function inferRoleFromSelected(){
        const opt = posSelect.options[posSelect.selectedIndex];
        if(!opt) return;
        const name = opt.getAttribute('data-name') || (opt.textContent.split(' - ')[0] || '');
        const slug = slugify(name);
        if(slug){
            roleSelect.value = slug;
            roleSelect.classList.add('border-warning');
            setTimeout(()=> roleSelect.classList.remove('border-warning'), 1200);
        }
    }
    posSelect.addEventListener('change', inferRoleFromSelected);
});
</script>
@endpush
@endsection
