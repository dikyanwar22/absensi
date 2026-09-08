<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - AbsensiKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *{font-family:'Poppins',sans-serif;}
        body{min-height:100vh; background: radial-gradient(1200px 600px at 10% -10%, #1e3a8a 0%, #0d6efd 35%, #0a58ca 65%, #061e4a 100%), #0d6efd; display:flex; align-items:center; justify-content:center; padding:24px;}
        .login-wrapper{max-width:1020px; width:100%; background:#fff; border-radius:24px; overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,0.25), 0 8px 24px rgba(0,0,0,0.15); display:grid; grid-template-columns:1fr 1fr;}
        .brand-panel{ background: linear-gradient(165deg, #0d6efd 0%, #0a4fc0 45%, #083a8a 100%); color:#fff; padding:40px 36px; position:relative; overflow:hidden; display:flex; flex-direction:column;}
        .brand-panel::before{content:""; position:absolute; inset:-40%; background: radial-gradient(500px 300px at 20% 20%, rgba(255,255,255,0.15), transparent 60%), radial-gradient(600px 400px at 90% 90%, rgba(255,255,255,0.08), transparent 60%);}
        .brand-panel > *{position:relative; z-index:1;}
        .logo-badge{width:56px;height:56px; background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.25); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:26px;}
        .feature-list{list-style:none; padding:0; margin:18px 0 0;}
        .feature-list li{display:flex; gap:12px; align-items:center; margin-bottom:14px; font-size:13px; opacity:0.95;}
        .feature-icon{width:36px;height:36px; background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.2); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;}
        .form-panel{padding:44px 40px; display:flex; flex-direction:column; justify-content:center; background:#fff;}
        .input-group-custom{position:relative;}
        .input-group-custom .form-control{height:46px; border-radius:12px; border:1px solid #e2e8f0; padding-left:42px; font-size:14px; background:#f8fafc; transition:all .2s;}
        .input-group-custom .form-control:focus{background:#fff; border-color:#0d6efd; box-shadow:0 0 0 4px rgba(13,110,253,0.1);}
        .input-icon{position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px; pointer-events:none;}
        .btn-login{height:46px; border-radius:12px; background: linear-gradient(135deg,#0d6efd,#0a58ca); border:none; font-weight:600; font-size:15px; letter-spacing:0.2px; box-shadow:0 8px 20px rgba(13,110,253,0.35); transition:all .2s;}
        .btn-login:hover{transform:translateY(-1px); box-shadow:0 12px 28px rgba(13,110,253,0.4); background: linear-gradient(135deg,#0a58ca,#083d8a);}
        .divider{height:1px; background:#e2e8f0; position:relative; margin:18px 0;}
        .demo-box{background:#f1f5f9; border:1px dashed #cbd5e1; border-radius:12px; padding:12px 14px; font-size:12px;}
        .demo-badge{font-size:10px; letter-spacing:0.4px;}
        @media(max-width:860px){
            .login-wrapper{grid-template-columns:1fr;}
            .brand-panel{padding:28px 24px;}
            .form-panel{padding:28px 22px;}
            body{padding:16px;}
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Branding -->
        <div class="brand-panel">
            <div>
                <div class="logo-badge mb-3"><i class="bi bi-fingerprint"></i></div>
                <h2 class="fw-bold mb-2" style="font-size:26px; line-height:1.2;">AbsensiKu</h2>
                <p class="mb-1" style="font-size:14px; opacity:0.9;">Sistem Absensi & Penggajian Terintegrasi</p>
                <p class="small" style="opacity:0.75; font-size:12.5px;">GPS + Foto Selfie • Approve 2 Level • Payroll Otomatis • Slip PDF</p>
            </div>

            <ul class="feature-list">
                <li><span class="feature-icon"><i class="bi bi-geo-alt-fill"></i></span><span><strong>Validasi GPS</strong> — radius geofence kantor, anti fake GPS</span></li>
                <li><span class="feature-icon"><i class="bi bi-camera-fill"></i></span><span><strong>Foto Selfie</strong> — wajib kamera, terkompres & tersimpan</span></li>
                <li><span class="feature-icon"><i class="bi bi-wallet2"></i></span><span><strong>Payroll Otomatis</strong> — lembur, telat, BPJS & THR</span></li>
            </ul>

            <div class="mt-auto pt-4">
                <div class="d-flex align-items-center gap-2 small" style="opacity:0.8; font-size:12px;">
                    <span><i class="bi bi-shield-check"></i> Laravel 10</span><span>•</span><span>AdminLTE 3</span><span>•</span><span>Bootstrap 5</span>
                </div>
                <div class="small mt-2" style="opacity:0.6; font-size:11px;">© {{ date('Y') }} AbsensiKu — HRD & Karyawan Mobile PWA</div>
            </div>
        </div>

        <!-- Right Form -->
        <div class="form-panel">
            <div class="mb-4">
                <h4 class="fw-bold mb-1" style="font-size:22px;">Masuk ke Akun</h4>
                <p class="text-muted small mb-0">Gunakan <strong>Email</strong> atau <strong>NIK</strong> + Password</p>
            </div>

            <x-auth-session-status class="mb-3" :status="session('status')" />

            @if($errors->any())
                <div class="alert alert-danger py-2 small d-flex gap-2 align-items-start" style="border-radius:12px; font-size:13px;">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold mb-1">Email / NIK</label>
                    <div class="input-group-custom">
                        <i class="bi bi-person input-icon"></i>
                        <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
                               class="form-control" placeholder="contoh@perusahaan.com atau NIK">
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label small fw-semibold mb-1">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small text-decoration-none" style="font-size:12px;">Lupa password?</a>
                        @endif
                    </div>
                    <div class="input-group-custom">
                        <i class="bi bi-lock input-icon"></i>
                        <input id="password" type="password" name="password" required class="form-control" placeholder="••••••••" autocomplete="current-password">
                        <button type="button" onclick="togglePass()" class="btn btn-sm position-absolute" style="right:6px; top:50%; transform:translateY(-50%); border:none; background:transparent; color:#64748b;"><i id="eyeIcon" class="bi bi-eye"></i></button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    <label class="d-flex align-items-center gap-2 small mb-0" style="cursor:pointer;">
                        <input id="remember_me" type="checkbox" name="remember" class="form-check-input m-0" style="width:16px;height:16px;">
                        <span class="text-muted">Ingat saya</span>
                    </label>
                    <span class="small text-muted" style="font-size:11px;"><i class="bi bi-shield-lock me-1"></i> Aman & terenkripsi</span>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100" id="btnLogin">
                    <span id="btnText"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang</span>
                    <span id="btnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-2" role="status"></span> Memproses...</span>
                </button>

                @if (Route::has('register'))
                    <p class="text-center small text-muted mt-3 mb-0">Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Daftar</a></p>
                @endif
            </form>

            <div class="demo-box mt-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <strong style="font-size:12px;"><i class="bi bi-info-circle me-1"></i> Akun Demo</strong>
                    <span class="badge bg-primary demo-badge">UJI COBA</span>
                </div>
                <div class="row g-2" style="font-size:11.5px;">
                    <div class="col-6"><div class="bg-white rounded p-2 border"><div class="fw-semibold"><i class="bi bi-person-badge me-1"></i> HRD</div><div class="text-muted">hrd@example.com</div><div class="text-muted">password</div></div></div>
                    <div class="col-6"><div class="bg-white rounded p-2 border"><div class="fw-semibold"><i class="bi bi-people me-1"></i> Staff</div><div class="text-muted">staff@example.com</div><div class="text-muted">password</div></div></div>
                </div>
                <div class="small text-muted mt-2" style="font-size:11px;"><i class="bi bi-lightbulb me-1"></i> NIK juga bisa dipakai jika sudah di-set HRD (contoh: STF001)</div>
            </div>

            <div class="text-center small text-muted mt-3" style="font-size:11px;">
                Dengan masuk Anda menyetujui <a href="#" class="text-decoration-none">Syarat Layanan</a> & <a href="#" class="text-decoration-none">Kebijakan Privasi</a>
            </div>
        </div>
    </div>

    <script>
        function togglePass(){
            const inp=document.getElementById('password');
            const icon=document.getElementById('eyeIcon');
            if(inp.type==='password'){ inp.type='text'; icon.className='bi bi-eye-slash'; }
            else{ inp.type='password'; icon.className='bi bi-eye'; }
        }
        document.getElementById('loginForm').addEventListener('submit', function(){
            const btn=document.getElementById('btnLogin');
            document.getElementById('btnText').classList.add('d-none');
            document.getElementById('btnSpinner').classList.remove('d-none');
            btn.disabled=true;
        });
    </script>
</body>
</html>
