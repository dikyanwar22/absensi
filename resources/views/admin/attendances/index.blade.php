@extends('layouts.admin')
@section('title','Absensi Harian')
@section('header','Absensi Harian - Lat/Lng Tracking')
@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="row">
            <div class="col-md-3"><input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="form-control"></div>
            <div class="col-md-2"><button class="btn btn-primary">Filter</button> <a href="/admin/attendances/live-map" class="btn btn-success"><i class="fas fa-map"></i> Live Map</a></div>
        </form>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead><tr><th>Karyawan</th><th>Shift</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Status</th><th>Lokasi Masuk (Lat/Lng)</th><th>Jarak</th><th>Foto</th><th style="width:90px;">Aksi HRD</th></tr></thead>
                <tbody>
                @forelse($attendances as $a)
                    <tr>
                        <td>{{ $a->user->name }}<br><small class="text-muted">{{ $a->user->nik }}</small><br><small class="text-muted">{{ $a->date?->format('d M Y') }}</small></td>
                        <td>{{ $a->shift->name ?? '-' }}</td>
                        <td>{{ $a->check_in ? \Carbon\Carbon::parse($a->check_in)->format('H:i') : '-' }}</td>
                        <td>{{ $a->check_out ? \Carbon\Carbon::parse($a->check_out)->format('H:i') : '-' }}</td>
                        <td>
                            @if($a->status=='hadir')<span class="badge bg-success">Hadir</span>
                            @elseif($a->status=='terlambat')<span class="badge bg-warning">Terlambat {{ $a->late_minutes }}m</span>
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
                        <td>{{ $a->distance_in_meter ? $a->distance_in_meter.'m' : '-' }}</td>
                        <td>
                            @if($a->photo_in)<a href="{{ asset('storage/'.$a->photo_in) }}" target="_blank"><img src="{{ asset('storage/'.$a->photo_in) }}" width="40" class="rounded"></a>@else - @endif
                            @if($a->photo_out)<a href="{{ asset('storage/'.$a->photo_out) }}" target="_blank"><img src="{{ asset('storage/'.$a->photo_out) }}" width="40" class="rounded ml-1"></a>@endif
                        </td>
                        <td>
                            <a href="{{ route('admin.attendances.edit',$a) }}" class="btn btn-xs btn-warning btn-sm"><i class="fas fa-edit"></i> Edit Jam</a>
                            <small class="d-block text-muted" style="font-size:10px;">HRD only</small>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Belum ada absensi hari ini</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $attendances->links() }}</div>
</div>
@endsection
