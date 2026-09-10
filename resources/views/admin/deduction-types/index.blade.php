@extends('layouts.admin')
@section('title','Master Potongan Dinamis')
@section('header','Master Potongan — HRD Input Nama + Default Rp (Auto Potong Saat Generate)')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title"><i class="fas fa-minus-circle"></i> Daftar Potongan</h3><br>
            <small class="text-muted">HRD input nama potongan bebas (Serikat, Liburan, Ganti Rugi, dll) + default Rp. Saat <strong>Generate Gaji</strong> otomatis terpotong & muncul per baris di slip.</small>
        </div>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalAdd"><i class="fas fa-plus"></i> Tambah Potongan</button>
    </div>
    <div class="card-body">
        <div class="alert alert-info small">
            <strong>Aturan:</strong> <code>Update</code> hanya berlaku periode berikutnya (slip lama tidak berubah). <code>Non-aktifkan / Hapus</code> = soft delete, history slip tetap aman. Jika salah input gaji/komplain → edit per karyawan di <code>Payroll → Detail → Edit Rp</code> (draft) atau Unlock → Edit.
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped datatable" style="width:100%">
                <thead><tr><th>#</th><th>Nama Potongan</th><th>Default Rp</th><th>Status</th><th>Deskripsi</th><th>Pembuat</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach($deductions as $d)
                <tr class="{{ $d->trashed() ? 'table-secondary' : '' }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $d->name }}</strong>
                        @if($d->trashed()) <span class="badge bg-dark">Archived (soft delete)</span> @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($d->default_amount,0,',','.') }}</td>
                    <td>
                        @if($d->trashed())
                            <span class="badge bg-dark">Non-aktif</span>
                        @elseif($d->is_active)
                            <span class="badge bg-success">Aktif — Auto Potong</span>
                        @else
                            <span class="badge bg-secondary">Non-aktif</span>
                        @endif
                    </td>
                    <td><small>{{ $d->description ?? '-' }}</small></td>
                    <td><small>{{ $d->creator->name ?? '-' }}</small></td>
                    <td>
                        @if($d->trashed())
                            <form method="POST" action="{{ route('admin.deduction-types.restore', $d->id) }}" class="d-inline">
                                @csrf <button class="btn btn-xs btn-success btn-sm" onclick="return confirm('Pulihkan potongan ini?')"><i class="fas fa-undo"></i> Pulihkan</button>
                            </form>
                        @else
                            <button class="btn btn-xs btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit{{ $d->id }}"><i class="fas fa-edit"></i> Edit</button>
                            <form method="POST" action="{{ route('admin.deduction-types.toggle', $d) }}" class="d-inline">
                                @csrf
                                @if($d->is_active)
                                    <button class="btn btn-xs btn-secondary btn-sm" onclick="return confirm('Non-aktifkan? Tidak akan terpotong di generate berikutnya, tapi slip lama tetap ada.')"><i class="fas fa-eye-slash"></i> Non-aktifkan</button>
                                @else
                                    <button class="btn btn-xs btn-success btn-sm"><i class="fas fa-eye"></i> Aktifkan</button>
                                @endif
                            </form>
                            <form method="POST" action="{{ route('admin.deduction-types.destroy', $d) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger btn-sm" onclick="return confirm('Hapus (soft delete)? History slip tetap aman. Bisa dipulihkan.')"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="modalAdd" tabindex="-1">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('admin.deduction-types.store') }}">
@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Potongan Baru</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body">
    <div class="form-group"><label>Nama Potongan <span class="text-danger">*</span> <small class="text-muted">(unique, ex: Potongan Serikat)</small></label><input type="text" name="name" class="form-control form-control-sm" required maxlength="100" placeholder="Potongan Serikat"></div>
    <div class="form-group"><label>Default Nominal Rp <span class="text-danger">*</span></label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="default_amount" class="form-control" required min="0" step="1000" value="0"></div><small class="text-muted">Akan otomatis terpotong tiap karyawan saat Generate.</small></div>
    <div class="form-group"><label>Deskripsi (opsional)</label><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Potongan rutin bulanan / insidentil"></textarea></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button><button class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button></div>
</form>
</div></div>
</div>

{{-- Modal Edit per row --}}
@foreach($deductions as $d)
@if(!$d->trashed())
<div class="modal fade" id="modalEdit{{ $d->id }}" tabindex="-1">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('admin.deduction-types.update', $d) }}">
@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit Potongan — {{ $d->name }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body">
    <div class="form-group"><label>Nama Potongan <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-sm" required maxlength="100" value="{{ $d->name }}"></div>
    <div class="form-group"><label>Default Nominal Rp <span class="text-danger">*</span></label><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="default_amount" class="form-control" required min="0" step="1000" value="{{ $d->default_amount }}"></div><small class="text-muted">Perubahan hanya untuk generate periode berikutnya.</small></div>
    <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control form-control-sm" rows="2">{{ $d->description }}</textarea></div>
    <div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" {{ $d->is_active ? 'checked' : '' }} id="is_active{{ $d->id }}"><label class="form-check-label" for="is_active{{ $d->id }}"> Aktif (auto potong saat generate)</label></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button><button class="btn btn-warning btn-sm"><i class="fas fa-save"></i> Update</button></div>
</form>
</div></div>
</div>
@endif
@endforeach
@endsection
