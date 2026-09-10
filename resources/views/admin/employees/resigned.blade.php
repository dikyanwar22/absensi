@extends('layouts.admin')
@section('title','Karyawan Resign')
@section('header','Karyawan Resign / Cut-Off')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif

<div class="alert alert-warning small"><i class="fas fa-info-circle"></i> Karyawan dengan status <strong>resigned</strong> atau <strong>non-aktif</strong> otomatis <strong>exclude dari generate payroll</strong> periode berikutnya (cek <code>PayrollController:52</code>). Histori tetap ada.</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Resigned ({{ $employees->count() }})</h3>
        <form method="GET" class="form-inline ml-auto">
                <div class="input-group input-group-sm" style="width:240px;">
                <input type="text" name="q" class="form-control" placeholder="Cari NIK/Nama" value="{{ request('q') }}">
                <div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search"></i></button></div>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable-resigned" class="table table-hover table-bordered table-striped datatable mb-0 text-sm" style="width:100%">
                <thead class="thead-light"><tr><th>#</th><th>Karyawan</th><th>Dept / Jabatan</th><th>Join - Resign</th><th>Status</th><th>Akun</th><th style="width:200px;">Aksi</th></tr></thead>
                <tbody>
                @forelse($employees as $i=>$e)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><strong>{{ $e->user->name ?? '-' }}</strong><br><small>NIK: {{ $e->user->nik ?? '-' }}</small></td>
                        <td><span class="badge bg-secondary">{{ $e->department->name ?? '-' }}</span><br><small>{{ $e->position->name ?? '-' }}</small></td>
                        <td><small>{{ $e->join_date?->format('d M Y') }}<br><i class="fas fa-arrow-right"></i> {{ $e->resign_date?->format('d M Y') ?? '-' }}</small></td>
                        <td><span class="badge bg-danger">Resigned</span> @if(!$e->is_active)<span class="badge bg-dark">Nonaktif</span>@endif</td>
                        <td>
                            @if(($e->user->status_account ?? 1)==0)
                                <span class="badge bg-danger"><i class="fas fa-ban"></i> Nonaktif</span><br><small class="text-muted">Tidak bisa login</small>
                            @else
                                <span class="badge bg-success"><i class="fas fa-check"></i> Aktif</span><br><small class="text-muted">Bisa login</small>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.employees.restore',$e) }}" class="d-inline" onsubmit="return confirm('Kembalikan {{ $e->user->name }} menjadi aktif? Status resigned akan dihapus, akun akan diaktifkan (status_account=1) sehingga bisa login kembali.')">
                                @csrf
                                <button class="btn btn-xs btn-success"><i class="fas fa-undo"></i> Aktifkan Kembali</button>
                            </form>
                            <a href="{{ route('admin.employees.edit',$e) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.employees.destroy',$e) }}" class="d-inline" onsubmit="return confirm('Hapus permanen {{ $e->user->name }} beserta akun login? Tidak bisa dikembalikan.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Hapus Permanen</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada karyawan resigned</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer"><small class="text-muted">Total {{ $employees->count() }} resigned — DataTables pagination aktif</small></div>
</div>
<a href="{{ route('admin.employees.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Data Karyawan</a>
@endsection
