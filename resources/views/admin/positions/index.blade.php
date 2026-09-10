@extends('layouts.admin')
@section('title','Jabatan')
@section('header','Master Jabatan')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h3 class="card-title">Daftar Jabatan ({{ $positions->count() }})</h3>
            <div class="d-flex gap-2 ml-auto">
                <form method="GET" class="form-inline">
                    <select name="department_id" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                        <option value="">-- Semua Dept --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group input-group-sm" style="width:200px;">
                        <input type="text" name="q" class="form-control" placeholder="Cari jabatan..." value="{{ request('q') }}">
                        <div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search"></i></button></div>
                    </div>
                </form>
                <a href="{{ route('admin.positions.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Jabatan</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable-positions" class="table table-hover table-bordered table-striped datatable mb-0" style="width:100%">
                <thead class="thead-light"><tr><th style="width:50px;">#</th><th>Jabatan</th><th>Departemen</th><th class="text-right">Gaji Pokok Default</th><th class="text-center">Karyawan</th><th style="width:160px;">Aksi</th></tr></thead>
                <tbody>
                @forelse($positions as $i=>$p)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><i class="fas fa-briefcase text-warning mr-1"></i> <strong>{{ $p->name }}</strong></td>
                        <td><span class="badge bg-primary">{{ $p->department->name ?? '-' }}</span></td>
                        <td class="text-right">Rp {{ number_format($p->basic_salary_default,0,',','.') }}</td>
                        <td class="text-center"><span class="badge bg-success">{{ $p->employees_count }}</span></td>
                        <td>
                            <a href="{{ route('admin.positions.edit',$p) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.positions.destroy',$p) }}" class="d-inline" onsubmit="return confirm('Hapus jabatan {{ $p->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada jabatan. <a href="{{ route('admin.positions.create') }}">Tambah</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <small class="text-muted">Total {{ $positions->count() }} jabatan — DataTables pagination aktif</small>
    </div>
</div>
<div class="alert alert-info small"><i class="fas fa-info-circle"></i> Gaji Pokok Default akan jadi acuan saat generate payroll (bisa override per periode).</div>
@endsection
