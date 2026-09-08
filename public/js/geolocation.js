/**
 * geolocation.js - Ambil lat/lng untuk absensi (improved)
 * public/js/geolocation.js:1
 */
function getLocationForAttendance(callback, errorCallback) {
    if (!navigator.geolocation) {
        if (errorCallback) errorCallback("Browser tidak support GPS. Gunakan Chrome terbaru.");
        return;
    }
    // Harus HTTPS atau localhost (127.0.0.1 / localhost dianggap secure)
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        if (errorCallback) errorCallback("GPS butuh HTTPS. Buka via https:// atau http://127.0.0.1:8000");
        return;
    }

    const opts = {enableHighAccuracy: true, timeout: 15000, maximumAge: 0};

    let responded = false;
    const timer = setTimeout(function(){
        if (!responded && errorCallback) errorCallback("Menunggu GPS terlalu lama (15s). Pastikan GPS aktif & di area terbuka, lalu klik Coba Lagi.");
    }, 16000);

    navigator.geolocation.getCurrentPosition(
        pos => {
            responded = true;
            clearTimeout(timer);
            const {latitude, longitude, accuracy} = pos.coords;
            // Deteksi fake GPS: beberapa plugin set coords.accuracy = 0 atau mocked flag
            const isMocked = pos.mocked || pos.coords.altitude === null && accuracy === 0 ? false : (pos.mocked || false);
            // Fallback: jika ada property isMocked dari native (Android), gunakan
            callback({
                lat: latitude,
                lng: longitude,
                accuracy: Math.round(accuracy),
                isMocked: isMocked
            });
        },
        err => {
            responded = true;
            clearTimeout(timer);
            let msg = "Gagal ambil lokasi: ";
            switch(err.code) {
                case 1: msg += "Izin lokasi ditolak. Tap ikon gembok di address bar → Izin Lokasi → Allow, lalu reload & Coba Lagi."; break;
                case 2: msg += "Posisi tidak tersedia. Pastikan GPS aktif, coba di area terbuka / dekat jendela, lalu Coba Lagi."; break;
                case 3: msg += "Timeout (15s). GPS lemah, coba lagi atau pindah ke area terbuka."; break;
                default: msg += err.message + " — coba Coba Lagi.";
            }
            if (errorCallback) errorCallback(msg);
            else alert(msg);
        },
        opts
    );
}

// Hitung Haversine di client untuk preview Dalam/Luar Jangkauan
function haversineClient(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}
