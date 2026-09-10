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
        <span class="badge bg-primary">{{ auth()->user()->display_role ?? 'STAFF' }}</span>
    </div>
</div>

@if(!$employee)
<div class="alert alert-warning py-2 small">Profil karyawan belum lengkap. Silakan lengkapi di <a href="{{ route('employee.profile') }}">Profile</a> atau hubungi HRD. Shift & lokasi pakai default sementara.</div>
@endif
<!-- Shift Hari Ini -->
<div class="card card-rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-start flex-nowrap gap-2">
        <div class="flex-grow-1" style="min-width:0;">
            <small class="text-muted">Shift Hari Ini</small>
            <div class="fw-semibold">{{ $employee?->shift?->name ?? 'Pagi' }} {{ $employee?->shift ? $employee->shift->start_time.' - '.$employee->shift->end_time : '07:00 - 15:00' }}</div>
            <small class="text-muted text-nowrap" style="white-space:nowrap;"><i class="bi bi-geo-alt"></i> {{ $office?->name ?? 'Kantor Pusat' }} (Radius {{ $office?->radius_meter ?? 100 }}m)</small>
        </div>
        @if(!$attendance)
            <span class="badge bg-danger align-self-start flex-shrink-0 text-nowrap">Belum Absen Masuk</span>
        @elseif($attendance->check_in && !$attendance->check_out)
            <span class="badge bg-warning align-self-start flex-shrink-0 text-nowrap">Sudah Masuk - Belum Pulang</span>
        @elseif($attendance->check_out)
            <span class="badge bg-success align-self-start flex-shrink-0 text-nowrap">Sudah Selesai</span>
        @endif
    </div>
</div>

<!-- Lokasi GPS -->
<style>
/* Fix: badge Luar/Dalam Jangkauan tidak boleh melebar keluar card di HP */
#location-status .badge {
    white-space: normal !important;
    overflow-wrap: anywhere;
    word-break: break-word;
    display: inline-block;
    max-width: 100%;
    line-height: 1.35;
    text-align: left;
    padding: 6px 8px;
    font-size: 11px;
}
#location-info { word-wrap: break-word; overflow-wrap: anywhere; }
</style>
<div class="card card-rounded p-3 mb-3" style="overflow:hidden; word-wrap:break-word;">
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">Lokasi Terdeteksi</small>
        <button type="button" id="btn-retry-gps" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px; display:none;" onclick="retryGps()"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</button>
    </div>
    <div id="location-info" class="small">
        <div class="text-muted"><span class="spinner-border spinner-border-sm me-1" style="width:14px;height:14px;"></span> Mencari lokasi... Aktifkan GPS & Allow Location</div>
    </div>
    <div id="location-status" class="mt-2">
        <span class="badge bg-secondary" style="white-space:normal; max-width:100%;">Menunggu GPS</span>
    </div>
    <small id="gps-help" class="text-muted mt-2 d-block" style="font-size:11px; display:none;">Tips: Aktifkan GPS, beri izin Lokasi (Allow), buka di Chrome, di area terbuka.</small>
</div>

<!-- Tombol Absen -->
<div class="card card-rounded p-3 mb-3 text-center">
    <div id="absen-info" class="mb-2 small text-muted">
        Jam Masuk: {{ $attendance?->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }} |
        Jam Pulang: {{ $attendance?->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}
        @if($attendance && $attendance->status=='terlambat') <span class="badge bg-warning">Terlambat {{ $attendance->late_minutes }}m</span> @endif
    </div>

    {{-- Tombol utama absen --}}
    <div id="absen-main">
        @if(!$attendance || !$attendance->check_in)
            <button id="btn-absen" class="btn btn-primary btn-absen w-100" style="background:linear-gradient(135deg,#0d6efd,#0a58ca); border:none;" onclick="openCamera('in')" disabled>
                <i class="bi bi-camera"></i> ABSEN MASUK
            </button>
        @elseif(!$attendance->check_out)
            <button id="btn-absen" class="btn btn-warning btn-absen w-100" style="border:none;" onclick="openCamera('out')" disabled>
                <i class="bi bi-box-arrow-right"></i> ABSEN PULANG
            </button>
        @else
            <button class="btn btn-success btn-absen w-100" disabled><i class="bi bi-check-circle"></i> Sudah Selesai Hari Ini</button>
        @endif
        <small class="text-muted mt-2 d-block">Foto Selfie + GPS Wajib</small>
    </div>

    {{-- Area Kamera & Preview --}}
    <div id="camera-area" class="d-none mt-2">
        <div class="position-relative">
            <video id="video" autoplay playsinline class="w-100 rounded" style="max-height:320px; background:#000; object-fit:cover;"></video>
            <img id="photo-preview" class="d-none w-100 rounded" style="max-height:320px; object-fit:cover;" />
            <canvas id="photo-canvas" class="d-none"></canvas>
        </div>
        <small id="camera-hint" class="text-muted d-block mt-2" style="font-size:11px;">Posisikan wajah di tengah, pastikan cahaya cukup, lalu klik <b>Ambil Foto</b></small>

        <div class="d-grid gap-2 mt-3">
            {{-- State 1: belum foto --}}
            <button id="btn-capture" class="btn btn-primary" onclick="takePhoto()"><i class="bi bi-camera-fill"></i> Ambil Foto</button>
            <button id="btn-cancel-camera" class="btn btn-outline-secondary" onclick="closeCamera()"><i class="bi bi-x-circle"></i> Batal</button>

            {{-- State 2: sudah foto --}}
            <button id="btn-retake" class="btn btn-outline-warning d-none" onclick="retakePhoto()"><i class="bi bi-arrow-repeat"></i> Ulang Foto</button>
            <button id="btn-save" class="btn btn-success d-none" onclick="saveAttendance()"><i class="bi bi-check-circle"></i> Simpan Gambar & Absen</button>
            <small id="save-hint" class="text-muted d-none" style="font-size:11px;">Periksa foto wajah sudah jelas, baru klik <b>Simpan Gambar</b>. Foto tidak akan tersimpan sebelum Anda klik simpan.</small>
        </div>
    </div>
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
let absenMode = 'in';
let capturedBase64 = null;

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
            const officeLat = {{ $office?->latitude ?? -6.2088 }}, officeLng = {{ $office?->longitude ?? 106.8456 }}, radius = {{ $office?->radius_meter ?? 100 }};
            const dist = haversineClient(loc.lat, loc.lng, officeLat, officeLng);
            const within = dist <= radius;
            // Fix HP: badge wrap ke bawah, tidak melebar keluar card
            document.getElementById('location-status').innerHTML = within
                ? `<span class="badge bg-success" style="white-space:normal; max-width:100%; display:inline-block; text-align:left;">Dalam Jangkauan <small>(${Math.round(dist)}m / ${radius}m)</small></span>`
                : `<span class="badge bg-danger" style="white-space:normal; max-width:100%; display:inline-block; text-align:left;">Luar Jangkauan <small>(${Math.round(dist)}m / ${radius}m)</small><br><small>- Pindah mendekati kantor</small></span>`;
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

async function openCamera(mode) {
    absenMode = mode || 'in';
    if(!currentLocation) {
        if(confirm('GPS belum siap. Coba ambil lokasi lagi?')){
            retryGps();
            setTimeout(()=>{ if(!currentLocation) alert('GPS masih belum siap. Tunggu Dalam Jangkauan hijau, lalu coba lagi.'); }, 800);
        }
        return;
    }
    try {
        // Minta kamera depan
        stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'}, audio:false});
        const video = document.getElementById('video');
        video.srcObject = stream;
        await video.play();

        document.getElementById('camera-area').classList.remove('d-none');
        document.getElementById('absen-main').classList.add('d-none');
        document.getElementById('video').classList.remove('d-none');
        document.getElementById('photo-preview').classList.add('d-none');
        document.getElementById('photo-preview').src = '';
        document.getElementById('btn-capture').classList.remove('d-none');
        document.getElementById('btn-cancel-camera').classList.remove('d-none');
        document.getElementById('btn-retake').classList.add('d-none');
        document.getElementById('btn-save').classList.add('d-none');
        document.getElementById('camera-hint').classList.remove('d-none');
        document.getElementById('save-hint').classList.add('d-none');
        capturedBase64 = null;
    } catch(e) {
        alert('Gagal buka kamera: '+e.message + '\nPastikan sudah Allow Camera di browser.');
    }
}

function takePhoto() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('photo-canvas');
    if(!video.videoWidth) {
        alert('Kamera belum siap, tunggu sebentar...');
        return;
    }
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    // Mirror selfie biar natural? tidak mirror hasil simpan
    ctx.drawImage(video,0,0, canvas.width, canvas.height);
    capturedBase64 = canvas.toDataURL('image/jpeg',0.7);

    document.getElementById('photo-preview').src = capturedBase64;
    document.getElementById('photo-preview').classList.remove('d-none');
    document.getElementById('video').classList.add('d-none');

    // Ganti tombol: sembunyikan Ambil, tampilkan Ulang & Simpan
    document.getElementById('btn-capture').classList.add('d-none');
    document.getElementById('btn-cancel-camera').classList.add('d-none');
    document.getElementById('btn-retake').classList.remove('d-none');
    document.getElementById('btn-save').classList.remove('d-none');
    document.getElementById('camera-hint').classList.add('d-none');
    document.getElementById('save-hint').classList.remove('d-none');
}

function retakePhoto() {
    capturedBase64 = null;
    document.getElementById('photo-preview').classList.add('d-none');
    document.getElementById('photo-preview').src = '';
    document.getElementById('video').classList.remove('d-none');
    document.getElementById('btn-capture').classList.remove('d-none');
    document.getElementById('btn-cancel-camera').classList.remove('d-none');
    document.getElementById('btn-retake').classList.add('d-none');
    document.getElementById('btn-save').classList.add('d-none');
    document.getElementById('camera-hint').classList.remove('d-none');
    document.getElementById('save-hint').classList.add('d-none');
}

function closeCamera() {
    if(stream) stream.getTracks().forEach(t=>t.stop());
    stream = null;
    capturedBase64 = null;
    document.getElementById('camera-area').classList.add('d-none');
    document.getElementById('absen-main').classList.remove('d-none');
    document.getElementById('video').classList.add('d-none');
    document.getElementById('video').srcObject = null;
    document.getElementById('photo-preview').classList.add('d-none');
    document.getElementById('photo-preview').src = '';
    document.getElementById('btn-capture').classList.remove('d-none');
    document.getElementById('btn-cancel-camera').classList.remove('d-none');
    document.getElementById('btn-retake').classList.add('d-none');
    document.getElementById('btn-save').classList.add('d-none');
    document.getElementById('camera-hint').classList.remove('d-none');
    document.getElementById('save-hint').classList.add('d-none');
}

function saveAttendance() {
    if(!capturedBase64) {
        alert('Silakan klik Ambil Foto dulu, pastikan wajah jelas baru Simpan Gambar');
        return;
    }
    if(!currentLocation) {
        alert('GPS belum siap');
        return;
    }
    const btnSave = document.getElementById('btn-save');
    const btnRetake = document.getElementById('btn-retake');
    btnSave.disabled = true;
    btnRetake.disabled = true;
    const originalSaveText = btnSave.innerHTML;
    btnSave.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Menyimpan...`;

    const url = absenMode === 'out' ? '/employee/attendance/check-out' : '/employee/attendance/check-in';
    fetch(url, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({
            lat: currentLocation.lat,
            lng: currentLocation.lng,
            accuracy: currentLocation.accuracy,
            is_mocked: currentLocation.isMocked,
            photo_base64: capturedBase64
        })
    }).then(async r=>{
        const data = await r.json();
        if(!r.ok) throw new Error(data.message || 'Gagal absen');
        if(stream) stream.getTracks().forEach(t=>t.stop());
        alert(data.message ?? 'Absen berhasil!');
        location.reload();
    }).catch(err=> {
        alert('Error: '+err.message);
        btnSave.disabled = false;
        btnRetake.disabled = false;
        btnSave.innerHTML = originalSaveText;
    });
}

// Legacy wrapper untuk kompatibilitas jika ada yang panggil doAbsen
function doAbsen(mode){ openCamera(mode); }
</script>
@endpush
