@extends('layouts.admin')
@section('title','Live Map')
@section('header','Live Map Absensi Hari Ini')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-3"><input type="date" class="form-control" value="{{ date('Y-m-d') }}" id="filter-date"></div>
            <div class="col-md-3"><select class="form-control" id="filter-dept"><option value="">Semua Departemen</option><option>IT</option><option>HRD</option></select></div>
            <div class="col-md-3"><select class="form-control" id="filter-status"><option value="">Semua Status</option><option value="hadir">Hadir</option><option value="terlambat">Terlambat</option></select></div>
            <div class="col-md-3"><button class="btn btn-primary w-100" onclick="loadMap()"><i class="fas fa-sync"></i> Refresh (Auto 30s)</button></div>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="live-map" style="height:600px;"></div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Marker Info</h3></div>
            <div class="card-body"><small class="text-muted">Hijau = Tepat, Kuning = Terlambat, Merah = Luar Radius, Abu = Belum Absen (di list kanan)</small></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Belum Absen Hari Ini (3)</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Andi - IT - Shift Pagi</li>
                    <li class="list-group-item">Rina - Produksi - Shift Siang</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
const map = L.map('live-map').setView([-6.2088, 106.8456], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
// Radius kantor
L.circle([-6.2088,106.8456], {radius:100, color:'blue', fillColor:'#0d6efd', fillOpacity:0.15}).addTo(map).bindPopup('Kantor Pusat - Radius 100m');

// Contoh marker (nanti via AJAX /admin/attendances/live-map-data)
const data = [
    {name:'Budi', lat:-6.2089, lng:106.8457, status:'hadir', time:'07:02', distance:12, dept:'IT'},
    {name:'Siti', lat:-6.2090, lng:106.8458, status:'terlambat', time:'07:25', distance:25, dept:'HRD'},
];

data.forEach(d=>{
    let color = d.status==='hadir' ? 'green' : d.status==='terlambat' ? 'orange' : 'red';
    const marker = L.circleMarker([d.lat, d.lng], {radius:8, color:color, fillColor:color, fillOpacity:0.8}).addTo(map);
    marker.bindPopup(`
        <b>${d.name}</b> (${d.dept})<br>
        Jam: ${d.time} - ${d.status}<br>
        Lat: ${d.lat}, Lng: ${d.lng}<br>
        Jarak: ${d.distance}m<br>
        <a href="https://www.google.com/maps?q=${d.lat},${d.lng}" target="_blank">Buka Google Maps</a>
    `);
});

function loadMap() {
    fetch('/admin/attendances/live-map-data?date='+document.getElementById('filter-date').value)
        .then(r=>r.json()).then(res=> console.log(res));
}
setInterval(loadMap, 30000);
</script>
@endpush
