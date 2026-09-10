<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - AbsensiKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{font-family:'Poppins',sans-serif;}
        body{min-height:100vh; background:#f1f5f9; display:flex; align-items:center; justify-content:center; padding:16px;}
        /* Smartphone container - seperti aplikasi absensi di HP */
        .phone-wrapper{width:100%; max-width:400px;}
        .phone-card{background:#fff; border-radius:28px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.12), 0 8px 24px rgba(0,0,0,0.08); border:1px solid #e2e8f0;}
        /* Header gradient dengan logo profesional */
        .login-header{background: linear-gradient(165deg, #0d6efd 0%, #0a4fc0 45%, #083a8a 100%); padding:32px 24px 28px; text-align:center; position:relative; overflow:hidden;}
        .login-header::before{content:""; position:absolute; inset:-50%; background: radial-gradient(400px 200px at 50% 0%, rgba(255,255,255,0.15), transparent 60%);}
        .login-header > *{position:relative; z-index:1;}
        .logo-wrap{width:72px; height:72px; background:#fff; border-radius:20px; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; box-shadow:0 8px 20px rgba(0,0,0,0.18); border:3px solid rgba(255,255,255,0.9);}
        .logo-wrap i{font-size:32px; color:#0d6efd;}
        .app-title{color:#fff; font-weight:700; font-size:22px; margin:0; letter-spacing:0.3px;}
        .app-subtitle{color:rgba(255,255,255,0.9); font-size:13px; margin:4px 0 0; font-weight:400;}
        .app-badge{display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.25); backdrop-filter:blur(8px); color:#fff; font-size:10px; padding:4px 10px; border-radius:20px; margin-top:10px; letter-spacing:0.4px;}
        /* Form area */
        .form-area{padding:24px 22px 22px; background:#fff;}
        .welcome{font-weight:700; font-size:18px; color:#0f172a; margin:0;}
        .welcome-sub{font-size:12.5px; color:#64748b; margin:4px 0 18px;}
        .input-label{font-size:12px; font-weight:600; color:#334155; margin-bottom:6px; display:block;}
        .input-group-custom{position:relative;}
        .input-group-custom .form-control{height:48px; border-radius:14px; border:1px solid #e2e8f0; padding-left:44px; padding-right:42px; font-size:14px; background:#f8fafc; transition:all .2s;}
        .input-group-custom .form-control:focus{background:#fff; border-color:#0d6efd; box-shadow:0 0 0 4px rgba(13,110,253,0.1);}
        .input-icon{position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px; pointer-events:none;}
        .toggle-pass{position:absolute; right:8px; top:50%; transform:translateY(-50%); border:none; background:transparent; color:#94a3b8; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:8px;}
        .toggle-pass:active{background:#f1f5f9;}
        .remember-row{display:flex; justify-content:space-between; align-items:center; margin:14px 0 18px;}
        .remember-label{display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; cursor:pointer;}
        .remember-label input{width:16px; height:16px; accent-color:#0d6efd;}
        .btn-login{height:50px; border-radius:14px; background: linear-gradient(135deg,#0d6efd,#0a58ca); border:none; font-weight:700; font-size:15px; letter-spacing:0.2px; box-shadow:0 10px 24px rgba(13,110,253,0.35); transition:all .2s;}
        .btn-login:active{transform:scale(0.98);}
        .divider{height:1px; background:#f1f5f9; margin:18px 0;}
        .demo-box{background:#f8fafc; border:1px dashed #cbd5e1; border-radius:14px; padding:12px;}
        .demo-title{font-size:11px; font-weight:700; color:#334155; display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;}
        .demo-grid{display:grid; grid-template-columns:1fr 1fr; gap:8px;}
        .demo-card{background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:8px 10px; text-align:center;}
        .demo-role{font-size:11px; font-weight:700; color:#0f172a;}
        .demo-creds{font-size:10.5px; color:#64748b; line-height:1.4;}
        .demo-tap{font-size:10px; color:#0d6efd; font-weight:600; margin-top:4px; cursor:pointer;}
        .footer-note{font-size:10px; color:#94a3b8; text-align:center; margin-top:14px; line-height:1.5;}
        .footer-note a{color:#64748b; text-decoration:none; font-weight:600;}
    </style>
</head>
<body>
    <div class="phone-wrapper">
        <!-- Kartu HP - mirip aplikasi absensi profesional -->
        <div class="phone-card">
            <!-- Header Logo -->
            <div class="login-header">
                <div class="logo-wrap"><i class="bi bi-fingerprint"></i></div>
                <h1 class="app-title">AbsensiKu</h1>
                <p class="app-subtitle">Absensi GPS + Foto Selfie</p>
            </div>

            <!-- Form -->
            <div class="form-area">
                <h2 class="welcome">Selamat Datang 👋</h2>
                <p class="welcome-sub">Masuk dengan <strong>Email</strong> atau <strong>NIK</strong> Anda</p>

                @if(session('status'))
                    <div class="alert alert-success py-2 px-3 small d-flex gap-2 align-items-start" style="border-radius:12px; font-size:12.5px; background:#dcfce7; border-color:#86efac; color:#166534;">
                        <i class="bi bi-check-circle-fill mt-1 flex-shrink-0"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif
                <x-auth-session-status class="mb-3" :status="session('status')" />

                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 small d-flex gap-2 align-items-start" style="border-radius:12px; font-size:12.5px;">
                        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    <div class="mb-3">
                        <label class="input-label" for="email">Email / NIK</label>
                        <div class="input-group-custom">
                            <i class="bi bi-person input-icon"></i>
                            <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
                                   class="form-control" placeholder="NIK / mail@gmail.com">
                        </div>
                    </div>

                    <div class="mb-1">
                        <div class="d-flex justify-content-between align-items-center" style="margin-bottom:6px;">
                            <label class="input-label mb-0" for="password" style="margin-bottom:0;">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size:11px; font-weight:600;">Lupa?</a>
                            @endif
                        </div>
                        <div class="input-group-custom">
                            <i class="bi bi-lock input-icon"></i>
                            <input id="password" type="password" name="password" required class="form-control" placeholder="••••••••" autocomplete="current-password">
                            <button type="button" onclick="togglePass()" class="toggle-pass"><i id="eyeIcon" class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <div class="remember-row">
                        <label class="remember-label">
                            <input id="remember_me" type="checkbox" name="remember"> Ingat saya
                        </label>
                        <span style="font-size:11px; color:#94a3b8;"><i class="bi bi-shield-lock me-1"></i>Aman</span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login w-100" id="btnLogin">
                        <span id="btnText"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang</span>
                        <span id="btnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-2" role="status"></span> Memproses...</span>
                    </button>

                    @if (Route::has('register'))
                        <p class="text-center small text-muted mt-3 mb-0" style="font-size:12px;">Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none fw-bold">Daftar</a></p>
                    @endif
                </form>

                <div class="footer-note">
                    © {{ date('Y') }} AbsensiKu — GPS + Foto Selfie • 1 Login untuk semua role<br>
                    Dengan masuk Anda menyetujui <a href="#">Syarat Layanan</a> & <a href="#">Privasi</a>
                </div>
            </div>
        </div>

        <div class="text-center mt-3" style="font-size:11px; color:#94a3b8;">
            <i class="bi bi-phone me-1"></i> Tampilan optimal di Smartphone • PWA Ready
        </div>
    </div>

    <script>
        function togglePass(){
            const inp=document.getElementById('email') ? document.getElementById('password') : null; // keep
            const pwd=document.getElementById('password');
            const icon=document.getElementById('eyeIcon');
            if(pwd.type==='password'){ pwd.type='text'; icon.className='bi bi-eye-slash'; }
            else{ pwd.type='password'; icon.className='bi bi-eye'; }
        }
        function fillDemo(email){
            document.getElementById('email').value=email;
            document.getElementById('password').value='password';
            document.getElementById('password').focus();
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
