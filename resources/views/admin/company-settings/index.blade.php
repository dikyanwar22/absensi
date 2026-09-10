@extends('layouts.admin')
@section('title','Pengaturan Perusahaan')
@section('header','Pengaturan Perusahaan — Logo, Nama & Alamat untuk Slip Gaji')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="row">
<div class="col-md-8">
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-building"></i> Identitas Perusahaan (Tampil di Slip Gaji)</h3></div>
    <form method="POST" action="{{ route('admin.company-settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="alert alert-info small">Logo, Nama & Alamat di sini akan otomatis muncul di header <code>/admin/payroll-detail/{id}/slip</code> dan <code>Slip Massal</code> serta download PDF karyawan. Jika logo kosong, slip tetap tampil nama & alamat teks.</div>

            <div class="form-group">
                <label>Nama Perusahaan <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required maxlength="150" value="{{ old('name',$company->name) }}" placeholder="PT. Contoh Sejahtera">
            </div>
            <div class="form-group">
                <label>Alamat Perusahaan</label>
                <textarea name="address" class="form-control" rows="2" maxlength="500" placeholder="Jl. Contoh No.1, Jakarta">{{ old('address',$company->address) }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6 form-group"><label>No. Telepon</label><input type="text" name="phone" class="form-control" maxlength="50" value="{{ old('phone',$company->phone) }}" placeholder="021-12345678"></div>
                <div class="col-md-6 form-group"><label>Email</label><input type="email" name="email" class="form-control" maxlength="100" value="{{ old('email',$company->email) }}" placeholder="hrd@perusahaan.com"></div>
            </div>
            <div class="form-group"><label>Website</label><input type="text" name="website" class="form-control" maxlength="150" value="{{ old('website',$company->website) }}" placeholder="https://perusahaan.com"></div>

            <div class="form-group">
                <label>Logo Perusahaan <small class="text-muted">(PNG/JPG, max 2MB, disarankan persegi / 300x300)</small></label>
                @if($company->logo_path)
                    <div class="mb-2 p-2 border rounded bg-light text-center">
                        <img src="{{ asset('storage/'.$company->logo_path) }}" alt="Logo" style="max-height:120px; max-width:200px; object-fit:contain;">
                        <br><small class="text-muted">{{ $company->logo_path }}</small>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="remove_logo" value="1" id="remove_logo">
                            <label class="form-check-label text-danger" for="remove_logo">Hapus logo (slip akan tanpa logo)</label>
                        </div>
                    </div>
                @endif
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg">
                    <label class="custom-file-label" for="logo">Pilih file logo baru...</label>
                </div>
                <small class="text-muted">Biarkan kosong jika tidak ganti logo. Upload baru akan mengganti yang lama otomatis.</small>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengaturan</button>
            <a href="{{ route('admin.payrolls.index') }}" class="btn btn-default">Kembali ke Payroll</a>
            @if($company->logo_path)
                <a href="{{ route('admin.company-settings.index') }}" onclick="window.open('{{ asset('storage/'.$company->logo_path) }}','_blank'); return false;" class="btn btn-info float-right"><i class="fas fa-eye"></i> Lihat Logo</a>
            @endif
        </div>
    </form>
</div>
</div>

<div class="col-md-4">
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Preview Slip Header</h3></div>
    <div class="card-body text-center">
        @if($company->logo_path)
            <img src="{{ asset('storage/'.$company->logo_path) }}" style="max-height:80px; max-width:140px; object-fit:contain;" class="mb-2 border p-1 bg-white">
        @else
            <div class="border bg-light p-3 mb-2"><small class="text-muted">(Belum ada logo — upload di form sebelah)</small></div>
        @endif
        <h6 class="mb-0" style="color:#0d6efd;">{{ $company->name }}</h6>
        <small class="text-muted">{{ $company->address ?? 'Alamat belum diisi' }}</small><br>
        <small class="text-muted">{{ $company->phone ?? '' }} {{ $company->email ? '• '.$company->email : '' }}</small>
        <hr>
        <small class="text-muted">Ini preview header yang akan muncul di PDF slip gaji. Buka contoh:</small><br>
        <a href="{{ \App\Models\PayrollDetail::first() ? route('admin.payrolls.slip', \App\Models\PayrollDetail::first()) : '#' }}" target="_blank" class="btn btn-sm btn-danger mt-2 {{ \App\Models\PayrollDetail::first() ? '' : 'disabled' }}"><i class="fas fa-file-pdf"></i> Lihat Slip Contoh</a>
        @if(!\App\Models\PayrollDetail::first())
            <br><small class="text-muted">Belum ada payroll detail — generate payroll dulu.</small>
        @endif
    </div>
</div>
<div class="alert alert-secondary small">
    <strong>Tips:</strong> Logo disimpan di <code>storage/app/public/company</code> dan otomatis di- symlink ke <code>public/storage</code>. Jalankan <code>php artisan storage:link</code> jika logo tidak tampil.
</div>
</div>
</div>
@push('js')
<script>
document.querySelector('.custom-file-input')?.addEventListener('change', function(e){
  var fileName = e.target.files[0]?.name || 'Pilih file logo baru...';
  e.target.nextElementSibling.innerText = fileName;
});
</script>
@endpush
@endsection
