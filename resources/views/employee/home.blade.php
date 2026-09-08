@extends('layouts.employee')
@section('title','Home')
@section('content')
<!-- Sapaan -->
<div class="card card-rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0">Selamat Pagi, {{ auth()->user()->name ?? 'Budi' }}!</h6>
            <small class="text-muted">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>
        <span class="badge bg-primary">Staff</span>
    </div>
</div>

<!-- Shift Hari Ini -->
<div class="card card-rounded p-3 mb-3">
    <div class="d-flex justify-content-between">
        <div>
            <small class="text-muted">Shift Hari Ini</small>
            <div class="fw-semibold">{{ $employee->shift->name ?? 'Pagi' }} {{ $employee->shift ? $employee->shift->start_time.' - '.$employee->shift->end_time : '07:00 - 15:00' }}</div>
            <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $office->name ?? 'Kantor Pusat' }} (Radius {{ $office->radius_meter ?? 100 }}m)</small>
        </div>
        @if(!$attendance)
            <span class="badge bg-danger align-self-start">Belum Absen Masuk</span>
        @elseif($attendance->check_in && !$attendance->check_out)
            <span class="badge bg-warning align-self-start">Sudah Masuk - Belum Pulang</span>
        @elseif($attendance->check_out)
            <span class="badge bg-success align-self-start">Sudah Pulang</span>
        @endif
    </div>
</div>

<!-- Lokasi GPS -->
<div class="card card-rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">Lokasi Terdeteksi</small>
        <button type="button" id="btn-retry-gps" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px; display:none;" onclick="retryGps()"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</button>
    </div>
    <div id="location-info" class="small">
        <div class="text-muted"><span class="spinner-border spinner-border-sm me-1" style="width:14px;height:14px;"></span> Mencari lokasi... Aktifkan GPS & Allow Location</div>
    </div>
    <div id="location-status" class="mt-2">
        <span class="badge bg-secondary">Menunggu GPS</span>
    </div>
    <small id="gps-help" class="text-muted mt-2 d-block" style="font-size:11px; display:none;">Tips: Aktifkan GPS, beri izin Lokasi (Allow), buka di Chrome, di area terbuka.</small>
</div>

<!-- Tombol Absen -->
<div class="card card-rounded p-3 mb-3 text-center">
    <div id="absen-info" class="mb-2 small text-muted">
        Jam Masuk: {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }} |
        Jam Pulang: {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}
        @if($attendance && $attendance->status=='terlambat') <span class="badge bg-warning">Terlambat {{ $attendance->late_minutes }}m</span> @endif
    </div>
    @if(!$attendance || !$attendance->check_in)
        <button id="btn-absen" class="btn btn-primary btn-absen w-100" style="background:linear-gradient(135deg,#0d6efd,#0a58ca); border:none;" onclick="doAbsen('in')" disabled>
            <i class="bi bi-camera"></i> ABSEN MASUK
        </button>
    @elseif(!$attendance->check_out)
        <button id="btn-absen" class="btn btn-warning btn-absen w-100" style="border:none;" onclick="doAbsen('out')" disabled>
            <i class="bi bi-box-arrow-right"></i> ABSEN PULANG
        </button>
    @else
        <button class="btn btn-success btn-absen w-100" disabled><i class="bi bi-check-circle"></i> Sudah Selesai Hari Ini</button>
    @endif
    <small class="text-muted mt-2 d-block">Foto Selfie + GPS Wajib (Lat/Lng tertracking)</small>
    <canvas id="photo-canvas" class="d-none"></canvas>
    <video id="video" autoplay playsinline class="d-none w-100 rounded mt-2" style="max-height:240px;"></video>
    <img id="photo-preview" class="d-none w-100 rounded mt-2" />
</div>

<!-- Pengumuman -->
<div class="card card-rounded p-3">
    <h6 class="mb-2"><i class="bi bi-megaphone"></i> Pengumuman</h6>
    <div class="small">
        <div class="border-start border-3 border-primary ps-2 mb-2">
            <div class="fw-semibold">Libur 17 Agustus</div>
            <small class="text-muted">Kantor tutup, absen tidak dihitung alpha</small>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
let currentLocation = null;
let stream = null;
let gpsAttempts = 0;

function setGpsLoading(){
    document.getElementById('location-info').innerHTML = `<div class="text-muted"><span class="spinner-border spinner-border-sm me-1" style="width:14px;height:14px;"></span> Mencari lokasi... (percobaan ${gpsAttempts+1})</div>`;
    document.getElementById('location-status').innerHTML = `<span class="badge bg-secondary">Menunggu GPS</span>`;
    document.getElementById('btn-retry-gps').style.display = 'none';
    document.getElementById('gps-help').style.display = 'none';
    const btn = document.getElementById('btn-absen');
    if(btn) btn.disabled = true;
}

function fetchLocation(){
    gpsAttempts++;
    setGpsLoading();
    getLocationForAttendance(
        loc => {
            currentLocation = loc;
            document.getElementById('location-info').innerHTML = `📍 ${loc.lat.toFixed(6)}, ${loc.lng.toFixed(6)} <br><small>Akurasi: ${loc.accuracy}m ${loc.accuracy>50 ? '<span class="text-warning">(GPS lemah)</span>': '<span class="text-success">(Baik)</span>'}</small>`;
            const officeLat = {{ $office->latitude ?? -6.2088 }}, officeLng = {{ $office->longitude ?? 106.8456 }}, radius = {{ $office->radius_meter ?? 100 }};
            const dist = haversineClient(loc.lat, loc.lng, officeLat, officeLng);
            const within = dist <= radius;
            document.getElementById('location-status').innerHTML = within
                ? `<span class="badge bg-success">Dalam Jangkauan (${Math.round(dist)}m / ${radius}m)</span>`
                : `<span class="badge bg-danger">Luar Jangkauan (${Math.round(dist)}m / ${radius}m) - Pindah mendekati kantor</span>`;
            const btn = document.getElementById('btn-absen');
            if(btn) btn.disabled = !within;
            if(loc.isMocked) {
                document.getElementById('location-status').innerHTML = `<span class="badge bg-danger">Fake GPS Terdeteksi! Matikan Mock Location</span>`;
                if(btn) btn.disabled = true;
            }
            document.getElementById('btn-retry-gps').style.display = 'none';
            document.getElementById('gps-help').style.display = 'none';
        },
        err => {
            document.getElementById('location-info').innerHTML = `<span class="text-danger" style="font-size:12px;">${err}</span>`;
            document.getElementById('location-status').innerHTML = `<span class="badge bg-danger">GPS Gagal</span>`;
            document.getElementById('btn-retry-gps').style.display = 'inline-block';
            document.getElementById('gps-help').style.display = 'block';
            const btn = document.getElementById('btn-absen');
            if(btn) btn.disabled = true;
        }
    );
}

function retryGps(){ fetchLocation(); }

// Ambil lokasi saat load
fetchLocation();

let absenMode = 'in';
async function doAbsen(mode) {
    absenMode = mode || 'in';
    if(!currentLocation) {
        // coba ambil ulang sekali sebelum menyerah
        if(confirm('GPS belum siap. Coba ambil lokasi lagi?')){
            retryGps();
            setTimeout(()=>{ if(!currentLocation) alert('GPS masih belum siap. Tunggu Dalam Jangkauan hijau, lalu coba lagi.'); }, 800);
        }
        return;
    }
    try {
        stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'}});
        const video = document.getElementById('video');
        video.srcObject = stream;
        video.classList.remove('d-none');
        setTimeout(()=> captureAndSubmit(), 800);
    } catch(e) {
        alert('Gagal buka kamera: '+e.message);
    }
}

function captureAndSubmit() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('photo-canvas');
    canvas.width = video.videoWidth; canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video,0,0);
    const base64 = canvas.toDataURL('image/jpeg',0.7);
    document.getElementById('photo-preview').src = base64;
    document.getElementById('photo-preview').classList.remove('d-none');
    if(stream) stream.getTracks().forEach(t=>t.stop());

    const url = absenMode === 'out' ? '/employee/attendance/check-out' : '/employee/attendance/check-in';
    fetch(url, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({
            lat: currentLocation.lat,
            lng: currentLocation.lng,
            accuracy: currentLocation.accuracy,
            is_mocked: currentLocation.isMocked,
            photo_base64: base64
        })
    }).then(async r=>{
        const data = await r.json();
        if(!r.ok) throw new Error(data.message || 'Gagal absen');
        alert(data.message ?? 'Absen berhasil!');
        location.reload();
    }).catch(err=> alert('Error: '+err.message));
}
</script>
@endpush
