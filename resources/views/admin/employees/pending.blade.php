@extends('layouts.admin')
@section('title','Akun Pending - Menunggu ACC HRD')
@section('header','Akun Pending (status_account=0)')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title">Menunggu Persetujuan HRD ({{ $employees->count() }}) - Akun tidak bisa login sampai diaktifkan</h3>
        <div class="card-tools">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info small"><i class="fas fa-info-circle"></i> Pada saat daftar akun, NIK di-generate otomatis (uniq, format NIK+YYMMDD+seq) dan status_account=0. HRD harus klik <strong>Aktifkan</strong> agar user bisa login (status_account=1). Tombol Nonaktifkan untuk blokir login lagi.</div>
        <form method="GET" class="form-inline mb-3">
            <div class="input-group input-group-sm" style="width:260px;">
                <input type="text" name="q" class="form-control" placeholder="Cari NIK/Nama/Email" value="{{ request('q') }}">
                <div class="input-group-append"><button class="btn btn-warning"><i class="fas fa-search"></i> Cari</button></div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped datatable text-sm">
                <thead class="thead-light"><tr><th>#</th><th>Foto</th><th>Karyawan</th><th>NIK Generate</th><th>Email</th><th>Tgl Daftar</th><th style="width:180px;">Aksi HRD</th></tr></thead>
                <tbody>
                @forelse($employees as $i=>$e)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>
                            @if($e->photo && file_exists(public_path('uploads/'.$e->photo)))
                                <img src="{{ asset('uploads/'.$e->photo) }}" width="32" height="32" class="rounded-circle" style="object-fit:cover;">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($e->user->name ?? 'K') }}&background=dc3545&color=fff&size=32" class="rounded-circle" width="32">
                            @endif
                        </td>
                        <td><strong>{{ $e->user->name ?? '-' }}</strong><br><small class="text-muted">{{ $e->user->role }}</small></td>
                        <td><span class="badge bg-primary">{{ $e->user->nik ?? '-' }}</span><br><small class="text-muted">NIK auto-generate uniq</small></td>
                        <td>{{ $e->user->email }}</td>
                        <td><small>{{ $e->created_at?->format('d M Y H:i') }}</small><br><span class="badge bg-danger">Pending</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.employees.toggle-status',$e) }}" onsubmit="return confirm('Aktifkan akun {{ $e->user->name }}? Setelah aktif user bisa login.')">
                                @csrf
                                <button class="btn btn-success btn-sm w-100"><i class="fas fa-check"></i> Aktifkan Akun</button>
                            </form>
                            <a href="{{ route('admin.employees.edit',$e) }}" class="btn btn-outline-secondary btn-sm w-100 mt-1"><i class="fas fa-edit"></i> Edit Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada akun pending. Semua akun sudah aktif.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
