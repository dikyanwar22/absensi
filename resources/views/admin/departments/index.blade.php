@extends('layouts.admin')
@section('title','Departemen')
@section('header','Master Departemen')
@section('content')

@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Departemen ({{ $departments->total() }})</h3>
        <div class="ml-auto d-flex gap-2">
            <form method="GET" class="form-inline mr-2">
                <div class="input-group input-group-sm" style="width:220px;">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama..." value="{{ request('q') }}">
                    <div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Departemen</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="thead-light">
                    <tr><th style="width:50px;">#</th><th>Nama Departemen</th><th>Deskripsi</th><th class="text-center">Jabatan</th><th class="text-center">Karyawan</th><th style="width:160px;">Aksi</th></tr>
                </thead>
                <tbody>
                @forelse($departments as $i => $d)
                    <tr>
                        <td>{{ $departments->firstItem() + $i }}</td>
                        <td><i class="fas fa-building text-primary mr-1"></i> <strong>{{ $d->name }}</strong></td>
                        <td><small class="text-muted">{{ $d->description ?: '-' }}</small></td>
                        <td class="text-center"><span class="badge bg-info">{{ $d->positions_count }}</span></td>
                        <td class="text-center"><span class="badge bg-success">{{ $d->employees_count }}</span></td>
                        <td>
                            <a href="{{ route('admin.departments.edit', $d) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.departments.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Hapus departemen {{ $d->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada departemen. <a href="{{ route('admin.departments.create') }}">Tambah sekarang</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Menampilkan {{ $departments->firstItem() ?? 0 }}-{{ $departments->lastItem() ?? 0 }} dari {{ $departments->total() }}</small>
        {{ $departments->links() }}
    </div>
</div>

<div class="alert alert-info small"><i class="fas fa-info-circle"></i> Tips: Departemen tidak bisa dihapus jika masih ada jabatan/karyawan terkait. Pindahkan dulu ke departemen lain.</div>
@endsection
