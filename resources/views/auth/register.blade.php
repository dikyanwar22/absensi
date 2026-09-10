<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar - AbsensiKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{font-family:'Poppins',sans-serif;}
        body{min-height:100vh; background:#f1f5f9; display:flex; align-items:center; justify-content:center; padding:16px;}
        .phone-wrapper{width:100%; max-width:400px;}
        .phone-card{background:#fff; border-radius:28px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.12), 0 8px 24px rgba(0,0,0,0.08); border:1px solid #e2e8f0;}
        .login-header{background: linear-gradient(165deg, #0d6efd 0%, #0a4fc0 45%, #083a8a 100%); padding:28px 24px 24px; text-align:center; position:relative; overflow:hidden;}
        .login-header::before{content:""; position:absolute; inset:-50%; background: radial-gradient(400px 200px at 50% 0%, rgba(255,255,255,0.15), transparent 60%);}
        .login-header > *{position:relative; z-index:1;}
        .logo-wrap{width:64px; height:64px; background:#fff; border-radius:18px; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; box-shadow:0 8px 20px rgba(0,0,0,0.18); border:3px solid rgba(255,255,255,0.9);}
        .logo-wrap i{font-size:28px; color:#0d6efd;}
        .app-title{color:#fff; font-weight:700; font-size:20px; margin:0;}
        .app-subtitle{color:rgba(255,255,255,0.9); font-size:12.5px; margin:4px 0 0;}
        .app-badge{display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.25); backdrop-filter:blur(8px); color:#fff; font-size:10px; padding:4px 10px; border-radius:20px; margin-top:10px; letter-spacing:0.3px;}
        .form-area{padding:22px 22px 20px; background:#fff;}
        .welcome{font-weight:700; font-size:17px; color:#0f172a; margin:0;}
        .welcome-sub{font-size:12px; color:#64748b; margin:4px 0 16px; line-height:1.5;}
        .input-label{font-size:11.5px; font-weight:600; color:#334155; margin-bottom:6px; display:block;}
        .input-group-custom{position:relative;}
        .input-group-custom .form-control{height:46px; border-radius:12px; border:1px solid #e2e8f0; padding-left:42px; font-size:13.5px; background:#f8fafc; transition:all .2s;}
        .input-group-custom .form-control:focus{background:#fff; border-color:#0d6efd; box-shadow:0 0 0 4px rgba(13,110,253,0.1);}
        .input-icon{position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:15px; pointer-events:none;}
        .toggle-pass{position:absolute; right:8px; top:50%; transform:translateY(-50%); border:none; background:transparent; color:#94a3b8; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:8px;}
        .btn-register{height:48px; border-radius:12px; background: linear-gradient(135deg,#0d6efd,#0a58ca); border:none; font-weight:700; font-size:14.5px; box-shadow:0 8px 20px rgba(13,110,253,0.32); transition:all .2s;}
        .btn-register:active{transform:scale(0.98);}
        .footer-note{font-size:10px; color:#94a3b8; text-align:center; margin-top:14px; line-height:1.5;}
        .footer-note a{color:#0d6efd; text-decoration:none; font-weight:600;}
    </style>
</head>
<body>
    <div class="phone-wrapper">
        <div class="phone-card">
            <div class="login-header">
                <div class="logo-wrap"><i class="bi bi-person-plus-fill"></i></div>
                <h1 class="app-title">Buat Akun</h1>
                <p class="app-subtitle">Daftar & mulai absen hari ini</p>
            </div>

            <div class="form-area">
                <h2 class="welcome">Daftar Akun Baru</h2>

                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 small d-flex gap-2 align-items-start" style="border-radius:12px; font-size:12px;">
                        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf
                    <div class="mb-3">
                        <label class="input-label" for="name">Nama Lengkap</label>
                        <div class="input-group-custom">
                            <i class="bi bi-person input-icon"></i>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                   class="form-control" placeholder="Budi Karyawan">
                        </div>
                        @error('name')<small class="text-danger" style="font-size:11px;">{{ $message }}</small>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="input-label" for="email">Email</label>
                        <div class="input-group-custom">
                            <i class="bi bi-envelope input-icon"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                   class="form-control" placeholder="nama@gmail.com">
                        </div>
                        @error('email')<small class="text-danger" style="font-size:11px;">{{ $message }}</small>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="input-label" for="password">Password</label>
                        <div class="input-group-custom">
                            <i class="bi bi-lock input-icon"></i>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                   class="form-control" placeholder="Buat password">
                            <button type="button" onclick="togglePass('password','eye1')" class="toggle-pass"><i id="eye1" class="bi bi-eye"></i></button>
                        </div>
                        @error('password')<small class="text-danger" style="font-size:11px;">{{ $message }}</small>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="input-label" for="password_confirmation">Konfirmasi Password</label>
                        <div class="input-group-custom">
                            <i class="bi bi-shield-lock input-icon"></i>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                   class="form-control" placeholder="Ulangi password">
                            <button type="button" onclick="togglePass('password_confirmation','eye2')" class="toggle-pass"><i id="eye2" class="bi bi-eye"></i></button>
                        </div>
                        @error('password_confirmation')<small class="text-danger" style="font-size:11px;">{{ $message }}</small>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-register w-100" id="btnRegister">
                        <span id="btnText"><i class="bi bi-person-plus me-1"></i> Daftar Sekarang</span>
                        <span id="btnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-2"></span> Memproses...</span>
                    </button>

                    <p class="text-center mt-3 mb-0" style="font-size:12.5px; color:#64748b;">Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Masuk di sini</a></p>
                </form>

                <div class="footer-note">
                    © {{ date('Y') }} AbsensiKu — Dengan daftar Anda menyetujui <a href="#">Syarat</a> & <a href="#">Privasi</a>
                </div>
            </div>
        </div>
        <div class="text-center mt-3" style="font-size:11px; color:#94a3b8;">
            <i class="bi bi-phone me-1"></i> Desain smartphone elegan • Sinkron dengan halaman login
        </div>
    </div>

    <script>
        function togglePass(id, eye){
            const inp=document.getElementById(id);
            const icon=document.getElementById(eye);
            if(inp.type==='password'){ inp.type='text'; icon.className='bi bi-eye-slash'; }
            else{ inp.type='password'; icon.className='bi bi-eye'; }
        }
        document.getElementById('registerForm').addEventListener('submit', function(){
            document.getElementById('btnText').classList.add('d-none');
            document.getElementById('btnSpinner').classList.remove('d-none');
            document.getElementById('btnRegister').disabled=true;
        });
    </script>
</body>
</html>
