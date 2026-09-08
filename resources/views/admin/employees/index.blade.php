@extends('layouts.admin')
@section('title','Data Karyawan')
@section('header','Data Karyawan')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <h3 class="card-title">Karyawan ({{ $employees->total() }})</h3>
            <div class="ml-auto d-flex flex-wrap gap-2">
                <form method="GET" class="form-inline">
                    <select name="department_id" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                        <option value="">-- Semua Dept --</option>
                        @foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
                    </select>
                    <select name="employment_status" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="kontrak" {{ request('employment_status')=='kontrak'?'selected':'' }}>Kontrak</option>
                        <option value="tetap" {{ request('employment_status')=='tetap'?'selected':'' }}>Tetap</option>
                        <option value="magang" {{ request('employment_status')=='magang'?'selected':'' }}>Magang</option>
                        <option value="probation" {{ request('employment_status')=='probation'?'selected':'' }}>Probation</option>
                        <option value="resigned" {{ request('employment_status')=='resigned'?'selected':'' }}>Resigned</option>
                    </select>
                    <div class="input-group input-group-sm" style="width:210px;">
                        <input type="text" name="q" class="form-control" placeholder="NIK/Nama/Email/Kode" value="{{ request('q') }}">
                        <div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search"></i></button></div>
                    </div>
                </form>
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Tambah Karyawan</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 text-sm">
                <thead class="thead-light"><tr><th>#</th><th>Foto</th><th>Karyawan</th><th>Dept / Jabatan</th><th>Shift / Lokasi</th><th>Status</th><th>Kontrak</th><th style="width:170px;">Aksi</th></tr></thead>
                <tbody>
                @forelse($employees as $i=>$e)
                    <tr @if(!$e->is_active) class="table-secondary" @endif>
                        <td>{{ $employees->firstItem()+$i }}</td>
                        <td>
                            @if($e->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($e->photo))
                                <img src="{{ asset('storage/'.$e->photo) }}" width="36" height="36" class="rounded-circle" style="object-fit:cover;">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($e->user->name ?? 'K') }}&background=0d6efd&color=fff&size=36" class="rounded-circle" width="36">
                            @endif
                        </td>
                        <td>
                            <strong>{{ $e->user->name ?? '-' }}</strong> @if(!$e->is_active)<span class="badge bg-secondary">Nonaktif</span>@endif<br>
                            <small class="text-muted">{{ $e->employee_code }} • {{ $e->user->nik ?? '-' }}</small><br>
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
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada karyawan. <a href="{{ route('admin.employees.create') }}">Tambah</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">{{ $employees->firstItem()??0 }}-{{ $employees->lastItem()??0 }} dari {{ $employees->total() }}</small>
        {{ $employees->links() }}
    </div>
</div>
@endsection
