<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password - AbsensiKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{font-family:'Poppins',sans-serif;}
        body{min-height:100vh; background:#f1f5f9; display:flex; align-items:center; justify-content:center; padding:16px;}
        .phone-wrapper{width:100%; max-width:400px;}
        .phone-card{background:#fff; border-radius:28px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.12), 0 8px 24px rgba(0,0,0,0.08); border:1px solid #e2e8f0;}
        .login-header{background: linear-gradient(165deg, #0d6efd 0%, #0a4fc0 45%, #083a8a 100%); padding:32px 24px 28px; text-align:center; position:relative; overflow:hidden;}
        .login-header::before{content:""; position:absolute; inset:-50%; background: radial-gradient(400px 200px at 50% 0%, rgba(255,255,255,0.15), transparent 60%);}
        .login-header > *{position:relative; z-index:1;}
        .logo-wrap{width:72px; height:72px; background:#fff; border-radius:20px; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; box-shadow:0 8px 20px rgba(0,0,0,0.18); border:3px solid rgba(255,255,255,0.9);}
        .logo-wrap i{font-size:32px; color:#0d6efd;}
        .app-title{color:#fff; font-weight:700; font-size:22px; margin:0; letter-spacing:0.3px;}
        .app-subtitle{color:rgba(255,255,255,0.9); font-size:13px; margin:4px 0 0; font-weight:400;}
        .form-area{padding:24px 22px 22px; background:#fff;}
        .welcome{font-weight:700; font-size:18px; color:#0f172a; margin:0;}
        .welcome-sub{font-size:12.5px; color:#64748b; margin:4px 0 18px; line-height:1.55;}
        .input-label{font-size:12px; font-weight:600; color:#334155; margin-bottom:6px; display:block;}
        .input-group-custom{position:relative;}
        .input-group-custom .form-control{height:48px; border-radius:14px; border:1px solid #e2e8f0; padding-left:44px; font-size:14px; background:#f8fafc; transition:all .2s;}
        .input-group-custom .form-control:focus{background:#fff; border-color:#0d6efd; box-shadow:0 0 0 4px rgba(13,110,253,0.1);}
        .input-icon{position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px; pointer-events:none;}
        .btn-reset{height:50px; border-radius:14px; background: linear-gradient(135deg,#0d6efd,#0a58ca); border:none; font-weight:700; font-size:14.5px; letter-spacing:0.2px; box-shadow:0 10px 24px rgba(13,110,253,0.35); transition:all .2s;}
        .btn-reset:active{transform:scale(0.98);}
        .info-box{background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:12px 14px; display:flex; gap:10px; align-items:start;}
        .info-box i{color:#0d6efd; font-size:16px; margin-top:2px;}
        .info-text{font-size:11.5px; color:#334155; line-height:1.5;}
        .footer-note{font-size:10px; color:#94a3b8; text-align:center; margin-top:16px; line-height:1.5;}
        .footer-note a{color:#64748b; text-decoration:none; font-weight:600;}
        .back-link{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; color:#0d6efd; text-decoration:none;}
        .back-link:hover{color:#0a58ca;}
    </style>
</head>
<body>
    <div class="phone-wrapper">
        <div class="phone-card">
            <div class="login-header">
                <div class="logo-wrap"><i class="bi bi-shield-lock-fill"></i></div>
                <h1 class="app-title">Lupa Password?</h1>
                <p class="app-subtitle">Reset aman • Link ke email terdaftar</p>
            </div>

            <div class="form-area">
                <h2 class="welcome">Atur Ulang Password</h2>
                <p class="welcome-sub">Masukkan <strong>email</strong> yang terdaftar. Kami akan kirimkan link untuk membuat password baru. Cek inbox & spam.</p>

                @if(session('status'))
                    <div class="alert alert-success py-2 px-3 small d-flex gap-2 align-items-start" style="border-radius:12px; font-size:12.5px; background:#dcfce7; border-color:#86efac; color:#166534;">
                        <i class="bi bi-check-circle-fill mt-1 flex-shrink-0"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 small d-flex gap-2 align-items-start" style="border-radius:12px; font-size:12.5px;">
                        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    </div>
                @endif

                <div class="info-box mb-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="info-text">
                        Link reset hanya berlaku <strong>60 menit</strong>. Jika tidak menerima email, periksa folder spam atau hubungi HRD.
                    </div>
                </div>

                <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                    @csrf
                    <div class="mb-3">
                        <label class="input-label" for="email">Email Terdaftar</label>
                        <div class="input-group-custom">
                            <i class="bi bi-envelope input-icon"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                   class="form-control" placeholder="nama@gmail.com">
                        </div>
                        <small class="text-muted" style="font-size:11px;">Gunakan email yang sama saat daftar (NIK tidak bisa untuk reset).</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-reset w-100" id="btnReset">
                        <span id="btnText"><i class="bi bi-send me-1"></i> Kirim Link Reset</span>
                        <span id="btnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-2" role="status"></span> Mengirim...</span>
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Masuk</a>
                    </div>
                    <p class="text-center small text-muted mt-2 mb-0" style="font-size:12px;">Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none fw-bold">Daftar</a></p>
                </form>

                <div class="footer-note">
                    © {{ date('Y') }} AbsensiKu — GPS + Foto Selfie<br>
                    Butuh bantuan? <a href="#">Hubungi HRD</a>
                </div>
            </div>
        </div>

        <div class="text-center mt-3" style="font-size:11px; color:#94a3b8;">
            <i class="bi bi-phone me-1"></i> Tampilan optimal di Smartphone • PWA Ready
        </div>
    </div>

    <script>
        document.getElementById('forgotForm').addEventListener('submit', function(){
            const btn=document.getElementById('btnReset');
            document.getElementById('btnText').classList.add('d-none');
            document.getElementById('btnSpinner').classList.remove('d-none');
            btn.disabled=true;
        });
    </script>
</body>
</html>
