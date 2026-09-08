@extends('layouts.admin')
@section('title','Karyawan Resign')
@section('header','Karyawan Resign / Cut-Off')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif

<div class="alert alert-warning small"><i class="fas fa-info-circle"></i> Karyawan dengan status <strong>resigned</strong> atau <strong>non-aktif</strong> otomatis <strong>exclude dari generate payroll</strong> periode berikutnya (cek <code>PayrollController:52</code>). Histori tetap ada.</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Resigned ({{ $employees->total() }})</h3>
        <form method="GET" class="form-inline ml-auto">
            <div class="input-group input-group-sm" style="width:240px;">
                <input type="text" name="q" class="form-control" placeholder="Cari NIK/Nama/Kode" value="{{ request('q') }}">
                <div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search"></i></button></div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 text-sm">
                <thead class="thead-light"><tr><th>#</th><th>Karyawan</th><th>Dept / Jabatan</th><th>Join - Resign</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($employees as $i=>$e)
                    <tr>
                        <td>{{ $employees->firstItem()+$i }}</td>
                        <td><strong>{{ $e->user->name ?? '-' }}</strong><br><small>{{ $e->employee_code }} • {{ $e->user->nik ?? '-' }}</small></td>
                        <td><span class="badge bg-secondary">{{ $e->department->name ?? '-' }}</span><br><small>{{ $e->position->name ?? '-' }}</small></td>
                        <td><small>{{ $e->join_date?->format('d M Y') }}<br><i class="fas fa-arrow-right"></i> {{ $e->resign_date?->format('d M Y') ?? '-' }}</small></td>
                        <td><span class="badge bg-danger">Resigned</span> @if(!$e->is_active)<span class="badge bg-dark">Nonaktif</span>@endif</td>
                        <td>
                            <a href="{{ route('admin.employees.edit',$e) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Detail/Edit</a>
                            <form method="POST" action="{{ route('admin.employees.destroy',$e) }}" class="d-inline" onsubmit="return confirm('Hapus permanen?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada karyawan resigned</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $employees->links() }}</div>
</div>
<a href="{{ route('admin.employees.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Data Karyawan</a>
@endsection
