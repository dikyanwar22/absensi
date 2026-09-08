<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - Absensi Karyawan</title>
    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @stack('css')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
     <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item"><a class="nav-link" href="#">{{ auth()->user()->role ?? 'HRD' }}</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            @php
                $adminPhoto = null;
                try {
                    $empAdmin = auth()->user()->employee ?? null;
                    if($empAdmin && $empAdmin->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($empAdmin->photo)){
                        $adminPhoto = asset('storage/'.$empAdmin->photo);
                    }
                } catch(\Throwable $e){}
                $adminAvatar = $adminPhoto ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name ?? 'HRD').'&background=0d6efd&color=fff&size=32';
            @endphp
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="{{ $adminAvatar }}" class="user-image img-circle elevation-2" alt="User Image" style="width:32px;height:32px;object-fit:cover;">
                    <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'HRD' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <li class="user-header bg-primary">
                        <img src="{{ $adminAvatar }}" class="img-circle elevation-2" alt="User Image" style="width:60px;height:60px;object-fit:cover;">
                        <p>
                            {{ auth()->user()->name ?? 'HRD' }}
                            <small>{{ auth()->user()->nik ?? '' }} • {{ auth()->user()->role ?? '' }}</small>
                            <small>{{ auth()->user()->email ?? '' }}</small>
                        </p>
                    </li>
                    <li class="user-footer d-flex justify-content-between">
                        <a href="{{ route('admin.profile.index') }}" class="btn btn-default btn-flat"><i class="fas fa-user"></i> Lihat Profile</a>
                        <a href="#" onclick="confirmLogoutAdmin(event)" class="btn btn-danger btn-flat"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/admin/dashboard" class="brand-link">
            <span class="brand-text font-weight-light">AbsensiKu HRD</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                    <li class="nav-item"><a href="/admin/dashboard" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                    <li class="nav-header">MASTER DATA</li>
                    <li class="nav-item"><a href="/admin/departments" class="nav-link"><i class="nav-icon fas fa-building"></i><p>Departemen</p></a></li>
                    <li class="nav-item"><a href="/admin/positions" class="nav-link"><i class="nav-icon fas fa-briefcase"></i><p>Jabatan</p></a></li>
                    <li class="nav-item"><a href="/admin/shifts" class="nav-link"><i class="nav-icon fas fa-clock"></i><p>Shift Kerja</p></a></li>
                    <li class="nav-item"><a href="/admin/office-locations" class="nav-link"><i class="nav-icon fas fa-map-marker-alt"></i><p>Lokasi Kantor</p></a></li>
                    <li class="nav-header">KARYAWAN</li>
                    <li class="nav-item"><a href="/admin/employees" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Data Karyawan</p></a></li>
                    <li class="nav-item"><a href="/admin/employees/resigned" class="nav-link"><i class="nav-icon fas fa-user-times"></i><p>Resign / Cut-Off</p></a></li>
                    <li class="nav-header">ABSENSI</li>
                    <li class="nav-item"><a href="/admin/attendances" class="nav-link"><i class="nav-icon fas fa-calendar-check"></i><p>Harian</p></a></li>
                    <li class="nav-item"><a href="/admin/attendances/live-map" class="nav-link"><i class="nav-icon fas fa-map"></i><p>Live Map Hari Ini</p></a></li>
                    <li class="nav-header">CUTI & PAYROLL</li>
                    <li class="nav-item"><a href="/admin/leaves" class="nav-link"><i class="nav-icon fas fa-envelope"></i><p>Pengajuan Cuti</p></a></li>
                    <li class="nav-item"><a href="/admin/payrolls" class="nav-link"><i class="nav-icon fas fa-money-bill"></i><p>Periode Gaji</p></a></li>
                    <li class="nav-item"><a href="/admin/reports" class="nav-link"><i class="nav-icon fas fa-file-excel"></i><p>Laporan & Export</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>@yield('header', 'Dashboard')</h1>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer"><strong>Laravel 10 + AdminLTE 3</strong> - Absensi Karyawan</footer>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmLogoutAdmin(e){
    e.preventDefault();
    Swal.fire({
        title: 'Yakin logout?',
        text: 'Anda akan keluar dari sesi admin',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal'
    }).then((result)=>{
        if(result.isConfirmed){ document.getElementById('logout-form').submit(); }
    });
}
</script>
@stack('js')
<form id="logout-form" action="/logout" method="POST" class="d-none">@csrf</form>
</body>
</html>
