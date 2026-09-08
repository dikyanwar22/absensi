@extends('layouts.admin')
@section('title','Dashboard')
@section('header','Dashboard HRD')
@section('content')
<div class="row">
    <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3>150</h3><p>Karyawan Aktif</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3>142</h3><p>Hadir Hari Ini</p></div><div class="icon"><i class="fas fa-check"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3>5</h3><p>Terlambat</p></div><div class="icon"><i class="fas fa-clock"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3>3</h3><p>Cuti Pending</p></div><div class="icon"><i class="fas fa-envelope"></i></div></div></div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Grafik Kehadiran 7 Hari</h3></div>
            <div class="card-body"><canvas id="chartHadir" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Kontrak Akan Habis (H-7)</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <tr><td>Budi (IT)</td><td><span class="badge bg-warning">3 hari</span></td></tr>
                    <tr><td>Siti (HRD)</td><td><span class="badge bg-danger">1 hari</span></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Live Map Hari Ini - <a href="/admin/attendances/live-map">Lihat Peta <i class="fas fa-external-link-alt"></i></a></h3></div>
    <div class="card-body p-0">
        <div id="mini-map" style="height:300px;"></div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('chartHadir'), {
    type:'bar',
    data:{
        labels:['Sen','Sel','Rab','Kam','Jum','Sab','Ming'],
        datasets:[{label:'Hadir', data:[145,142,148,140,142,30,20], backgroundColor:'#0d6efd'}]
    }
});
// Mini Leaflet
const map = L.map('mini-map').setView([-6.2088, 106.8456], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.circle([-6.2088,106.8456], {radius:100, color:'blue', fillOpacity:0.1}).addTo(map);
L.marker([-6.2088,106.8456]).addTo(map).bindPopup('Kantor Pusat');
</script>
@endpush
