<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>@yield('title', 'Karyawan') - AbsensiKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Poppins',sans-serif; background:#f1f5f9;}
        .mobile-container{max-width:480px; margin:0 auto; background:#f8f9fa; min-height:100vh; padding-bottom:80px; box-shadow:0 0 20px rgba(0,0,0,0.05);}
        .bottom-bar{max-width:480px; margin:0 auto; height:65px; background:#fff; border-top:1px solid #e9ecef;}
        .fab-home{width:60px; height:60px; margin-top:-20px; background:linear-gradient(135deg,#0d6efd,#0a58ca); border:3px solid #fff; box-shadow:0 4px 12px rgba(13,110,253,0.4);}
        .card-rounded{border-radius:16px; border:none; box-shadow:0 2px 8px rgba(0,0,0,0.06);}
        .btn-absen{height:56px; border-radius:14px; font-weight:600; font-size:18px;}
    </style>
    @stack('css')
</head>
<body>
<div class="mobile-container">
    <!-- Top Bar -->
    <nav class="navbar bg-white shadow-sm sticky-top px-3" style="max-width:480px; margin:0 auto;">
        <span class="fw-bold text-primary">AbsensiKu</span>
        <div class="d-flex align-items-center gap-3">
            <span id="live-clock" class="small fw-semibold">08:00:00</span>
            @php
                $notifCount = 0;
                if(auth()->check()){
                    $uid = auth()->id();
                    try {
                        $notifCount += \App\Models\Leave::where('user_id',$uid)->where('final_status','pending')->count();
                        $notifCount += \App\Models\PayrollDetail::where('user_id',$uid)->whereHas('payroll', fn($q)=>$q->where('status','locked'))->count();
                        $today = \Carbon\Carbon::today()->toDateString();
                        $hasToday = \App\Models\Attendance::where('user_id',$uid)->whereDate('date',$today)->exists();
                        if(!$hasToday) $notifCount += 1;
                        if(\Illuminate\Support\Facades\Schema::hasTable('announcements')){
                            $notifCount += \Illuminate\Support\Facades\DB::table('announcements')->where('is_active',1)->count();
                        }
                        $emp = auth()->user()->employee ?? null;
                        if($emp && $emp->contract_end_date && \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($emp->contract_end_date), false) >=0 && \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($emp->contract_end_date), false) <=30) $notifCount += 1;
                    } catch(\Throwable $e) { $notifCount = 0; }
                }
            @endphp
            <a href="{{ route('employee.notifications') }}" class="text-dark position-relative"><i class="bi bi-bell fs-5"></i>@if($notifCount>0)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px;">{{ $notifCount>9 ? '9+' : $notifCount }}</span>@endif</a>
            @php
                $topPhoto = null;
                try {
                    $empTop = auth()->user()->employee ?? null;
                    if($empTop && $empTop->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($empTop->photo)){
                        $topPhoto = asset('storage/'.$empTop->photo);
                    }
                } catch(\Throwable $e){}
                $topAvatar = $topPhoto ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name ?? 'Karyawan').'&background=0d6efd&color=fff';
            @endphp
            <div class="dropdown">
                <a href="#" class="d-block" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ $topAvatar }}" class="rounded-circle" width="32" height="32" style="object-fit:cover; border:2px solid #0d6efd;">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:180px;">
                    <li class="px-3 py-2 text-center border-bottom">
                        <img src="{{ $topAvatar }}" class="rounded-circle mb-2" width="48" height="48" style="object-fit:cover;">
                        <div class="fw-semibold small">{{ auth()->user()->name ?? 'Karyawan' }}</div>
                        <small class="text-muted" style="font-size:11px;">{{ auth()->user()->nik ?? '' }} • {{ auth()->user()->role ?? '' }}</small>
                    </li>
                    <li><a class="dropdown-item small" href="{{ route('employee.profile') }}"><i class="bi bi-person me-2"></i> Lihat Profile</a></li>
                    <li><a class="dropdown-item small text-danger" href="#" onclick="confirmLogoutEmployee(event)"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="p-3">
        @yield('content')
    </div>
</div>

<!-- Bottom Bar FIXED -->
<div class="fixed-bottom bottom-bar d-flex justify-content-around align-items-center text-center">
    <a href="/employee/menu" class="text-decoration-none text-muted flex-fill py-2"><div><i class="bi bi-grid fs-5"></i></div><small style="font-size:10px;">Menu</small></a>
    <a href="/employee/history" class="text-decoration-none text-muted flex-fill py-2"><div><i class="bi bi-calendar-check fs-5"></i></div><small style="font-size:10px;">Riwayat</small></a>
    <a href="/employee/home" class="flex-fill d-flex justify-content-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fab-home"><i class="bi bi-fingerprint fs-3"></i></div>
    </a>
    <a href="/employee/payslip" class="text-decoration-none text-muted flex-fill py-2"><div><i class="bi bi-wallet2 fs-5"></i></div><small style="font-size:10px;">Slip</small></a>
    <a href="/employee/profile" class="text-decoration-none text-muted flex-fill py-2"><div><i class="bi bi-person fs-5"></i></div><small style="font-size:10px;">Profile</small></a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/geolocation.js') }}"></script>
<script>
    // Live Clock
    setInterval(()=>{ document.getElementById('live-clock').textContent = new Date().toLocaleTimeString('id-ID'); },1000);
    function confirmLogoutEmployee(e){
        e.preventDefault();
        Swal.fire({
            title: 'Yakin logout?',
            text: 'Anda akan keluar dari aplikasi',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then((result)=>{
            if(result.isConfirmed){
                // create and submit logout form
                let form=document.createElement('form');
                form.method='POST'; form.action='/logout';
                let token=document.createElement('input'); token.type='hidden'; token.name='_token'; token.value='{{ csrf_token() }}';
                form.appendChild(token);
                document.body.appendChild(form); form.submit();
            }
        });
    }
</script>
@stack('js')
</body>
</html>
