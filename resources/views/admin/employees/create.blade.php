@extends('layouts.admin')
@section('title','Tambah Karyawan')
@section('header','Tambah Karyawan Baru')
@section('content')
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
<form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data">
@csrf
<div class="row">
<div class="col-md-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Akun Login</h3></div>
        <div class="card-body">
            <div class="form-group"><label>Nama Lengkap <span class="text-danger">*</span></label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required></div>
            <div class="form-group"><label>NIK (opsional unik)</label><input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}" placeholder="NIK KTP"> @error('nik')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required> @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label>Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required> @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label>Role <span class="text-danger">*</span> <small class="text-muted">(otomatis dari jabatan)</small></label>
                <input type="text" name="role" id="role" class="form-control @error('role') is-invalid @enderror" value="{{ old('role') }}" placeholder="otomatis: manager_finance" list="roleList">
                <datalist id="roleList">
                    @php $existingRoles = \App\Models\User::distinct()->pluck('role')->filter(); @endphp
                    @foreach($existingRoles as $r)<option value="{{ $r }}">@endforeach
                    <option value="hrd"><option value="supervisor"><option value="staff">
                </datalist>
                <small class="text-muted" style="font-size:11px;">Otomatis dari jabatan (slug manager_finance), bisa diubah manual jika perlu.</small>
                @error('role')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
            </div>
            <div class="form-group"><label>Foto (opsional)</label><input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*"> @error('photo')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Data Kepegawaian</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 form-group"><label>Departemen <span class="text-danger">*</span></label><select name="department_id" class="form-control" required>@foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach</select></div>
                <div class="col-6 form-group"><label>Jabatan <span class="text-danger">*</span></label><select name="position_id" id="position_id" class="form-control" required>@foreach($positions as $p)<option value="{{ $p->id }}" {{ old('position_id')==$p->id?'selected':'' }} data-name="{{ $p->name }}">{{ $p->name }} - {{ $p->department->name }} (Rp {{ number_format($p->basic_salary_default,0,',','.') }})</option>@endforeach</select></div>
            </div>
            <div class="row">
                <div class="col-6 form-group"><label>Shift</label><select name="shift_id" class="form-control"><option value="">-- Pilih Shift --</option>@foreach($shifts as $s)<option value="{{ $s->id }}" {{ old('shift_id')==$s->id?'selected':'' }}>{{ $s->name }} ({{ $s->start_time }}-{{ $s->end_time }})</option>@endforeach</select></div>
                <div class="col-6 form-group"><label>Lokasi Kantor</label><select name="office_location_id" class="form-control"><option value="">-- Default Aktif --</option>@foreach($offices as $o)<option value="{{ $o->id }}" {{ old('office_location_id')==$o->id?'selected':'' }}>{{ $o->name }}</option>@endforeach</select></div>
            </div>
            <div class="form-group"><label>No. HP</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div>
            <div class="form-group"><label>Alamat</label><textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea></div>
            <div class="row">
                <div class="col-6 form-group"><label>Tgl Masuk <span class="text-danger">*</span></label><input type="date" name="join_date" class="form-control" value="{{ old('join_date', date('Y-m-d')) }}" required></div>
                <div class="col-6 form-group"><label>Status <span class="text-danger">*</span></label><select name="employment_status" class="form-control" required><option value="kontrak">Kontrak</option><option value="tetap">Tetap</option><option value="magang">Magang</option><option value="probation">Probation</option><option value="resigned">Resigned</option></select></div>
            </div>
            <div class="form-group"><label>Akhir Kontrak (jika kontrak)</label><input type="date" name="contract_end_date" class="form-control" value="{{ old('contract_end_date') }}"></div>
            <div class="row">
                <div class="col-6 form-group"><label>Bank</label><input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" placeholder="BCA"></div>
                <div class="col-6 form-group"><label>No Rekening</label><input type="text" name="bank_account" class="form-control" value="{{ old('bank_account') }}"></div>
            </div>
            <div class="row">
                <div class="col-6 form-group"><label>BPJS Kes</label><input type="text" name="bpjs_kes" class="form-control" value="{{ old('bpjs_kes') }}"></div>
                <div class="col-6 form-group"><label>BPJS TK</label><input type="text" name="bpjs_tk" class="form-control" value="{{ old('bpjs_tk') }}"></div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan Karyawan</button>
        </div>
    </div>
</div>
</div>
</form>
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const posSelect = document.getElementById('position_id');
    const roleInput = document.getElementById('role');
    if(!posSelect || !roleInput) return;
    function slugify(str){
        return str.toLowerCase().trim().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'');
    }
    posSelect.addEventListener('change', function(){
        const opt = posSelect.options[posSelect.selectedIndex];
        const name = opt ? (opt.getAttribute('data-name') || opt.textContent.split(' - ')[0]) : '';
        const slug = slugify(name);
        if(slug){
            roleInput.value = slug;
            roleInput.classList.add('border-warning');
            setTimeout(()=> roleInput.classList.remove('border-warning'), 1200);
        }
    });
});
</script>
@endpush
@endsection
