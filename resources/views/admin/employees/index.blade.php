@extends('layouts.admin')
@section('title','Data Karyawan')
@section('header','Data Karyawan')
@push('css')
<style>
    .filter-card .form-control{height:38px; border-radius:8px; font-size:13px;}
    .filter-card label{font-size:11px; font-weight:600; color:#334155; margin-bottom:4px; display:block; white-space:nowrap;}
    .filter-actions .btn{height:38px; border-radius:8px; font-size:13px; font-weight:600; padding:0 14px; display:inline-flex; align-items:center; gap:6px; white-space:nowrap;}
    @media (max-width: 576px){
        .filter-actions .btn{flex:1 1 auto; justify-content:center;}
    }
</style>
@endpush
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

@if(isset($pendingCount) && $pendingCount>0)
<div class="alert alert-warning d-flex justify-content-between align-items-center py-2">
    <span><i class="fas fa-exclamation-triangle mr-1"></i> Ada <strong>{{ $pendingCount }}</strong> akun pending menunggu ACC HRD (status_account=0, tidak bisa login)</span>
    <a href="{{ route('admin.employees.pending') }}" class="btn btn-warning btn-sm"><i class="fas fa-user-clock"></i> Lihat Pending</a>
</div>
@endif
<div class="card filter-card">
    <div class="card-header bg-white">
        <form method="GET" class="row" style="margin:-6px;">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="department_id">Departemen</label>
                <select name="department_id" id="department_id" class="form-control">
                    <option value="">-- Semua Dept --</option>
                    @foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="employment_status">Status</label>
                <select name="employment_status" id="employment_status" class="form-control">
                    <option value="">-- Semua Status --</option>
                    <option value="kontrak" {{ request('employment_status')=='kontrak'?'selected':'' }}>Kontrak</option>
                    <option value="tetap" {{ request('employment_status')=='tetap'?'selected':'' }}>Tetap</option>
                    <option value="magang" {{ request('employment_status')=='magang'?'selected':'' }}>Magang</option>
                    <option value="probation" {{ request('employment_status')=='probation'?'selected':'' }}>Probation</option>
                    <option value="resigned" {{ request('employment_status')=='resigned'?'selected':'' }}>Resigned</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="status_account">Akun</label>
                <select name="status_account" id="status_account" class="form-control">
                    <option value="">-- Semua Akun --</option>
                    <option value="1" {{ request('status_account')==='1'?'selected':'' }}>Aktif</option>
                    <option value="0" {{ request('status_account')==='0'?'selected':'' }}>Pending</option>
                </select>
            </div>
                    <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3" style="padding:6px;">
                <label for="q">NIK / Nama / Email</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="NIK / Nama / Email" value="{{ request('q') }}">
            </div>
            <div class="col-12 col-lg-8 col-xl-3 d-flex align-items-end" style="padding:6px;">
                <div class="filter-actions d-flex flex-wrap w-100" style="gap:6px;">
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                    <a href="{{ route('admin.employees.index', ['all'=>1]) }}" class="btn btn-outline-secondary"><i class="fas fa-list"></i> Semua</a>
                    <a href="{{ route('admin.employees.pending') }}" class="btn btn-warning"><i class="fas fa-user-clock"></i> Pending @if(isset($pendingCount) && $pendingCount>0)<span class="badge badge-light ml-1">{{ $pendingCount }}</span>@endif</a>
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Tambah</a>
                </div>
            </div>
        </form>
    </div>
    @php $hasFilter = request()->filled('q') || request()->filled('department_id') || request()->filled('employment_status') || request()->filled('status_account') || request()->filled('all'); @endphp
    @if(!$hasFilter)
        <div class="alert alert-info mx-3 mt-3 mb-0"><i class="fas fa-info-circle"></i> <b>Default kosong agar tidak lelet (1000+ karyawan).</b> Silakan gunakan filter di atas (departemen, status, akun, atau cari NIK/nama) lalu klik <b>Filter</b>. Klik <b>Semua</b> untuk memuat semua.</div>
    @endif
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable-employees" class="table table-hover table-bordered table-striped datatable mb-0 text-sm" style="width:100%">
                <thead class="thead-light"><tr><th>#</th><th>Foto</th><th>Karyawan</th><th>Dept / Jabatan</th><th>Shift / Lokasi</th><th>Status</th><th>Kontrak</th><th>Akun</th><th style="width:200px;">Aksi</th></tr></thead>
                <tbody>
                @forelse($employees as $i=>$e)
                    <tr @if(!$e->is_active) class="table-secondary" @endif>
                        <td>{{ $i+1 }}</td>
                        <td>
                            @if($e->photo && file_exists(public_path('uploads/'.$e->photo)))
                                <img src="{{ asset('uploads/'.$e->photo) }}" width="36" height="36" class="rounded-circle" style="object-fit:cover;">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($e->user->name ?? 'K') }}&background=0d6efd&color=fff&size=36" class="rounded-circle" width="36">
                            @endif
                        </td>
                        <td>
                            <strong>{{ $e->user->name ?? '-' }}</strong> @if(!$e->is_active)<span class="badge bg-secondary">Nonaktif</span>@endif<br>
                            <small class="text-muted">NIK: {{ $e->user->nik ?? '-' }}</small><br>
                            <small>{{ $e->user->email ?? '-' }} • {{ $e->user->role ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $e->department->name ?? '-' }}</span><br>
                            <small>{{ $e->position->name ?? '-' }}</small><br>
                            <small class="text-muted">Rp {{ number_format($e->position->basic_salary_default ?? 0,0,',','.') }}</small>
                        </td>
                        <td>
                            <small>{{ $e->shift->name ?? '-' }} {{ $e->shift ? '('.\Carbon\Carbon::parse($e->shift->start_time)->format('H:i').')' : '' }}</small><br>
                            <small class="text-muted">{{ $e->officeLocation->name ?? '-' }}</small>
                        </td>
                        <td>
                            @if($e->employment_status=='kontrak')<span class="badge bg-warning">Kontrak</span>
                            @elseif($e->employment_status=='tetap')<span class="badge bg-success">Tetap</span>
                            @elseif($e->employment_status=='resigned')<span class="badge bg-danger">Resigned</span>
                            @else<span class="badge bg-info">{{ ucfirst($e->employment_status) }}</span>@endif
                            <br><small class="text-muted">{{ $e->join_date?->format('d M Y') }}</small>
                        </td>
                        <td>
                            @if($e->contract_end_date)
                                @php $days=\Carbon\Carbon::now()->diffInDays($e->contract_end_date,false); @endphp
                                <small>{{ $e->contract_end_date->format('d M Y') }}</small><br>
                                @if($days<0)<span class="badge bg-danger">Habis {{ abs($days) }}h lalu</span>
                                @elseif($days<=7)<span class="badge bg-warning">H-{{ $days }}</span>
                                @else<span class="badge bg-success">Sisa {{ $days }}h</span>@endif
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            @if(($e->user->status_account ?? 1)==1)
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Aktif</span><br><small class="text-muted">Bisa login</small>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-clock"></i> Pending</span><br><small class="text-muted">Belum ACC</small>
                            @endif
                            <form method="POST" action="{{ route('admin.employees.toggle-status',$e) }}" class="mt-1" onsubmit="return confirm('Ubah status akun {{ $e->user->name }} ?')">
                                @csrf
                                @if(($e->user->status_account ?? 1)==1)
                                    <button class="btn btn-xs btn-outline-danger w-100"><i class="fas fa-ban"></i> Nonaktifkan</button>
                                @else
                                    <button class="btn btn-xs btn-success w-100"><i class="fas fa-check"></i> Aktifkan</button>
                                @endif
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('admin.employees.edit',$e) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <button class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#resignModal{{ $e->id }}"><i class="fas fa-user-times"></i> Resign</button>
                            <form method="POST" action="{{ route('admin.employees.destroy',$e) }}" class="d-inline" onsubmit="return confirm('Hapus karyawan {{ $e->user->name }}? Hapus juga akun login!')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                            </form>

                            <!-- Resign Modal -->
                            <div class="modal fade" id="resignModal{{ $e->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <form method="POST" action="{{ route('admin.employees.resign',$e) }}">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header"><h5 class="modal-title">Cut-Off Resign</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                            <div class="modal-body">
                                                <p class="small">Set status <strong>resigned</strong>, non-aktifkan akun, exclude dari payroll periode berikutnya.</p>
                                                <div class="form-group">
                                                    <label>Tgl Resign</label>
                                                    <input type="date" name="resign_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-danger">Resign</button></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    @if(!$hasFilter)
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-filter"></i> Silakan filter untuk menampilkan data (default kosong agar cepat, tidak load 1000 data) — klik <b>Semua</b> jika ingin lihat semua.</td></tr>
                    @else
                        <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data sesuai filter. <a href="{{ route('admin.employees.index') }}">Reset</a></td></tr>
                    @endif
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <small class="text-muted">Total {{ $employees->count() }} karyawan — DataTables pagination aktif (cari & filter instan di tabel)</small>
    </div>
</div>
@endsection
