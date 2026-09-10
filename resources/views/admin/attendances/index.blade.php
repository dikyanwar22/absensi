@extends('layouts.admin')
@section('title','Absensi Harian')
@section('header','Absensi Harian - Lat/Lng Tracking')
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
<div class="card filter-card">
    <div class="card-header bg-white">
        <form method="GET" class="row" style="margin: -6px;">
            {{-- Cari NIK / Email / Nama --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-3" style="padding:6px;">
                <label for="q">NIK / Email / Nama</label>
                <input type="text" name="q" id="q" value="{{ request('q') }}" class="form-control" placeholder="NIK / Email / Nama">
            </div>
            {{-- Departemen --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="department_id">Departemen</label>
                <select name="department_id" id="department_id" class="form-control">
                    <option value="">-- Semua --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Jabatan --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="position_id">Jabatan</label>
                <select name="position_id" id="position_id" class="form-control">
                    <option value="">-- Semua --</option>
                    @foreach($positions as $p)
                        <option value="{{ $p->id }}" {{ request('position_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Shift --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="shift_id">Shift</label>
                <select name="shift_id" id="shift_id" class="form-control">
                    <option value="">-- Semua --</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}" {{ request('shift_id')==$s->id?'selected':'' }}>{{ $s->name }} ({{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }})</option>
                    @endforeach
                </select>
            </div>
            {{-- Status --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">-- Semua --</option>
                    <option value="hadir" {{ request('status')=='hadir'?'selected':'' }}>Hadir</option>
                    <option value="terlambat" {{ request('status')=='terlambat'?'selected':'' }}>Terlambat</option>
                    <option value="pulang_cepat" {{ request('status')=='pulang_cepat'?'selected':'' }}>Pulang Cepat</option>
                    <option value="alpha" {{ request('status')=='alpha'?'selected':'' }}>Alpha / Tidak Hadir</option>
                </select>
            </div>
            {{-- Jarak --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="distance">Jarak</label>
                <select name="distance" id="distance" class="form-control">
                    <option value="">-- Semua --</option>
                    <option value=">10" {{ request('distance')=='>10'?'selected':'' }}>&gt;10m (luar)</option>
                    <option value="<=10" {{ request('distance')=='<=10'?'selected':'' }}>≤10m (dalam)</option>
                    <option value=">50" {{ request('distance')=='>50'?'selected':'' }}>&gt;50m (jauh)</option>
                </select>
            </div>
            {{-- Tanggal mulai --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="start_date">Tgl Mulai (1)</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>
            {{-- Tanggal akhir --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="end_date">Tgl Akhir (31)</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>
            {{-- Tanggal tunggal --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" style="padding:6px;">
                <label for="date">Tgl Tunggal</label>
                <input type="date" name="date" id="date" value="{{ request('date') }}" class="form-control">
            </div>
            {{-- Tombol --}}
            <div class="col-12 col-lg-12 col-xl-4 d-flex align-items-end" style="padding:6px;">
                <div class="filter-actions d-flex flex-wrap w-100" style="gap:6px;">
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                    <a href="{{ route('admin.attendances.export', request()->query()) }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Export</a>
                    <a href="/admin/attendances/live-map" class="btn btn-info"><i class="fas fa-map"></i> Map</a>
                </div>
            </div>
            <div class="col-12" style="padding:6px 6px 0 6px;">
                <small class="text-muted"><i class="fas fa-info-circle"></i> Isi <b>start - end</b> untuk 1-31/custom (contoh 2026-09-01 s/d 2026-09-30) atau <b>tgl tunggal</b>. Export mengikuti filter.</small>
            </div>
        </form>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible m-3 mb-0"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger m-3 mb-0"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

    @php $hasFilter = request()->filled('q') || request()->filled('department_id') || request()->filled('position_id') || request()->filled('shift_id') || request()->filled('status') || request()->filled('distance') || request()->filled('start_date') || request()->filled('end_date') || request()->filled('date'); @endphp
    @if(!$hasFilter)
        <div class="alert alert-info mx-3 mt-3 mb-0"><i class="fas fa-info-circle"></i> <b>Default kosong agar tidak lelet (1000+ data).</b> Silakan gunakan filter di atas (NIK/nama, departemen, jabatan, shift, status, jarak, tanggal 1-31/custom) lalu klik <b>Filter</b> untuk menampilkan data. Export juga mengikuti filter.</div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable-attendances" class="table table-sm table-bordered table-striped datatable" style="width:100%">
                <thead><tr>
                    <th>Karyawan</th>
                    <th>Dept / Jabatan</th>
                    <th>Tgl</th>
                    <th>Shift</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Status</th>
                    <th>Lokasi Masuk (Lat/Lng)</th>
                    <th>Jarak</th>
                    <th>Foto</th>
                    <th style="width:90px;">Aksi HRD</th>
                </tr></thead>
                <tbody>
                @forelse($attendances as $a)
                    <tr>
                        <td>
                            {{ $a->user->name }}<br>
                            <small class="text-muted">NIK: {{ $a->user->nik }}</small><br>
                            <small class="text-muted">{{ $a->user->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $a->user->employee->department->name ?? '-' }}</span><br>
                            <small>{{ $a->user->employee->position->name ?? '-' }}</small>
                        </td>
                        <td><small>{{ $a->date?->format('d M Y') }}</small></td>
                        <td>
                            @if($a->shift)
                                <span class="badge" style="background: {{ $a->shift->color ?? '#6c757d' }}">{{ $a->shift->name }}</span><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($a->shift->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($a->shift->end_time)->format('H:i') }}</small>
                            @else - @endif
                        </td>
                        <td>{{ $a->check_in ? \Carbon\Carbon::parse($a->check_in)->format('H:i') : '-' }}</td>
                        <td>{{ $a->check_out ? \Carbon\Carbon::parse($a->check_out)->format('H:i') : '-' }}</td>
                        <td>
                            @if($a->status=='hadir')<span class="badge bg-success">Hadir</span>
                            @elseif($a->status=='terlambat')<span class="badge bg-warning">Terlambat {{ $a->late_minutes }}m</span>
                            @elseif($a->status=='alpha')<span class="badge bg-danger">Tidak Hadir</span>
                            @else<span class="badge bg-secondary">{{ $a->status }}</span>@endif
                            @if($a->is_fake_gps)<span class="badge bg-danger">Fake GPS</span>@endif
                            @if($a->overtime_hours>0)<br><small class="badge bg-info">Lembur {{ $a->overtime_hours }}j</small>@endif
                        </td>
                        <td>
                            @if($a->lat_in)
                                <small>{{ $a->lat_in }}, {{ $a->lng_in }}</small><br>
                                <a href="https://www.google.com/maps?q={{ $a->lat_in }},{{ $a->lng_in }}" target="_blank" class="btn btn-xs btn-outline-primary btn-sm"><i class="fas fa-map-marker-alt"></i> Maps</a>
                                <small class="text-muted">Ak: {{ $a->accuracy_in }}m</small>
                            @else - @endif
                        </td>
                        <td>
                            @if($a->distance_in_meter)
                                @if($a->distance_in_meter > 10)
                                    <span class="badge bg-danger">{{ $a->distance_in_meter }}m &gt;10</span>
                                @else
                                    <span class="badge bg-success">{{ $a->distance_in_meter }}m</span>
                                @endif
                            @else -
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <div class="text-center">
                                    @if($a->photo_in)
                                        <a href="{{ asset('uploads/'.$a->photo_in) }}" target="_blank"><img src="{{ asset('uploads/'.$a->photo_in) }}" width="42" height="42" style="object-fit:cover;" class="rounded border"></a>
                                    @else
                                        <span class="d-inline-block bg-light border rounded d-flex align-items-center justify-content-center" style="width:42px;height:42px;font-size:10px;">-</span>
                                    @endif
                                    <small class="d-block text-muted" style="font-size:9px;">Masuk</small>
                                </div>
                                <div class="text-center">
                                    @if($a->photo_out)
                                        <a href="{{ asset('uploads/'.$a->photo_out) }}" target="_blank"><img src="{{ asset('uploads/'.$a->photo_out) }}" width="42" height="42" style="object-fit:cover;" class="rounded border"></a>
                                    @else
                                        <span class="d-inline-block bg-light border rounded d-flex align-items-center justify-content-center" style="width:42px;height:42px;font-size:10px;">-</span>
                                    @endif
                                    <small class="d-block text-muted" style="font-size:9px;">Pulang</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.attendances.edit',$a) }}" class="btn btn-xs btn-warning btn-sm"><i class="fas fa-edit"></i> Edit Jam</a>
                            <small class="d-block text-muted" style="font-size:10px;">HRD only</small>
                        </td>
                    </tr>
                @empty
                    @if(!$hasFilter)
                        <tr><td colspan="11" class="text-center text-muted py-4"><i class="fas fa-filter"></i> Silakan filter untuk menampilkan data (default kosong agar cepat, tidak load 1000 data)</td></tr>
                    @else
                        <tr><td colspan="11" class="text-center text-muted">Tidak ada data sesuai filter</td></tr>
                    @endif
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer"><small class="text-muted">Total {{ $attendances->count() }} data — Export Excel mudah dibaca (18 kolom: NIK,Nama,Dept,Jabatan,Tanggal,Shift,Jam,Status,Jarak,Lat/Lng,Fake GPS)</small></div>
</div>
@endsection
