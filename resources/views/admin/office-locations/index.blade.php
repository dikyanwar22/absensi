@extends('layouts.admin')
@section('title','Lokasi Kantor')
@section('header','Lokasi Kantor (Geofence Latitude/Longitude)')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Lokasi ({{ $locations->count() }})</h3>
        <a href="{{ route('admin.office-locations.create') }}" class="btn btn-primary btn-sm ml-auto"><i class="fas fa-plus"></i> Tambah Lokasi</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable-locations" class="table table-hover table-bordered table-striped datatable mb-0" style="width:100%">
                <thead class="thead-light"><tr><th>#</th><th>Nama</th><th>Alamat</th><th>Latitude / Longitude</th><th>Radius</th><th>Status</th><th>Karyawan</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($locations as $i=>$loc)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><i class="fas fa-map-marker-alt text-danger mr-1"></i> <strong>{{ $loc->name }}</strong></td>
                        <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($loc->address,40) ?: '-' }}</small></td>
                        <td><small>{{ $loc->latitude }}, {{ $loc->longitude }}</small><br><a href="https://www.google.com/maps?q={{ $loc->latitude }},{{ $loc->longitude }}" target="_blank" class="badge bg-info">Google Maps</a></td>
                        <td><span class="badge bg-primary">{{ $loc->radius_meter }} m</span></td>
                        <td>@if($loc->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Nonaktif</span>@endif</td>
                        <td class="text-center"><span class="badge bg-success">{{ $loc->employees_count }}</span></td>
                        <td style="min-width:140px;">
                            <a href="{{ route('admin.office-locations.edit',$loc) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.office-locations.destroy',$loc) }}" class="d-inline" onsubmit="return confirm('Hapus {{ $loc->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada lokasi. <a href="{{ route('admin.office-locations.create') }}">Tambah Kantor Pusat</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($locations->isNotEmpty())
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-map"></i> Preview Peta Semua Lokasi</h3></div>
    <div class="card-body p-0"><div id="map-all" style="height:380px;"></div></div>
</div>
@push('js')
<script>
const locs = @json($locations);
const map = L.map('map-all').setView([locs[0]?.latitude || -6.2088, locs[0]?.longitude || 106.8456], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
locs.forEach(l=>{
    const lat = parseFloat(l.latitude), lng = parseFloat(l.longitude);
    L.marker([lat,lng]).addTo(map).bindPopup(`<b>${l.name}</b><br>${l.address||''}<br>Radius: ${l.radius_meter}m`);
    L.circle([lat,lng], {radius: parseInt(l.radius_meter), color: l.is_active ? 'blue' : 'gray', fillOpacity:0.12}).addTo(map);
});
</script>
@endpush
@endif
@endsection
