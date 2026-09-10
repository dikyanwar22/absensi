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
    <!-- DataTables Bootstrap 4 + Responsive (AdminLTE 3) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    <style>
        /* DataTables custom untuk AdminLTE */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: #007bff !important; color:#fff !important; border-color:#007bff !important; }
        table.dataTable thead th { white-space: nowrap; }
        .dt-search { margin-bottom: 8px; }
    </style>
    @stack('css')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
     <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item"><a class="nav-link" href="#">{{ auth()->user()->display_role ?? 'HRD' }}</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            @php
                $adminPhoto = null;
                try {
                    $empAdmin = auth()->user()->employee ?? null;
                    if($empAdmin && $empAdmin->photo && file_exists(public_path('uploads/'.$empAdmin->photo))){
                        $adminPhoto = asset('uploads/'.$empAdmin->photo);
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
                            <small>{{ auth()->user()->nik ?? '' }} • {{ auth()->user()->display_role ?? '' }}</small>
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
                @php
                    $can = fn($key) => \App\Helpers\MenuHelper::can($key);
                @endphp
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    @if($can('dashboard'))<li class="nav-item"><a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>@endif
                    @if($can('departments')||$can('positions')||$can('shifts')||$can('office_locations'))<li class="nav-header">MASTER DATA</li>@endif
                    @if($can('departments'))<li class="nav-item"><a href="/admin/departments" class="nav-link {{ request()->is('admin/departments*') ? 'active' : '' }}"><i class="nav-icon fas fa-building"></i><p>Departemen</p></a></li>@endif
                    @if($can('positions'))<li class="nav-item"><a href="/admin/positions" class="nav-link {{ request()->is('admin/positions*') ? 'active' : '' }}"><i class="nav-icon fas fa-briefcase"></i><p>Jabatan</p></a></li>@endif
                    @if($can('shifts'))<li class="nav-item"><a href="/admin/shifts" class="nav-link {{ request()->is('admin/shifts*') ? 'active' : '' }}"><i class="nav-icon fas fa-clock"></i><p>Shift Kerja</p></a></li>@endif
                    @if($can('office_locations'))<li class="nav-item"><a href="/admin/office-locations" class="nav-link {{ request()->is('admin/office-locations*') ? 'active' : '' }}"><i class="nav-icon fas fa-map-marker-alt"></i><p>Lokasi Kantor</p></a></li>@endif
                    @if($can('employees')||$can('employees_pending')||$can('employees_resigned')||$can('menu_settings'))<li class="nav-header">KARYAWAN</li>@endif
                    @if($can('employees'))<li class="nav-item"><a href="/admin/employees" class="nav-link {{ request()->is('admin/employees') || request()->is('admin/employees/*/edit') || request()->is('admin/employees/create') ? 'active' : '' }}"><i class="nav-icon fas fa-users"></i><p>Data Karyawan</p></a></li>@endif
                    @php $pendingNav = 0; try { $pendingNav = \App\Models\User::where('status_account',0)->count(); } catch(\Throwable $e){} @endphp
                    @if($can('employees_pending'))<li class="nav-item"><a href="/admin/employees/pending" class="nav-link {{ request()->is('admin/employees/pending*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-clock"></i><p>Akun Pending @if($pendingNav>0)<span class="badge badge-warning right">{{ $pendingNav }}</span>@endif</p></a></li>@endif
                    @if($can('employees_resigned'))<li class="nav-item"><a href="/admin/employees/resigned" class="nav-link {{ request()->is('admin/employees/resigned*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-times"></i><p>Resign / Cut-Off</p></a></li>@endif
                    @if($can('menu_settings'))<li class="nav-item"><a href="/admin/menu-settings" class="nav-link {{ request()->is('admin/menu-settings*') ? 'active' : '' }}"><i class="nav-icon fas fa-cogs"></i><p>Setting Menu</p></a></li>@endif
                    @if($can('attendances')||$can('live_map'))<li class="nav-header">ABSENSI</li>@endif
                    @if($can('attendances'))<li class="nav-item"><a href="/admin/attendances" class="nav-link {{ request()->is('admin/attendances') || request()->is('admin/attendances/*/edit') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-check"></i><p>Harian</p></a></li>@endif
                    @if($can('live_map'))<li class="nav-item"><a href="/admin/attendances/live-map" class="nav-link {{ request()->is('admin/attendances/live-map*') ? 'active' : '' }}"><i class="nav-icon fas fa-map"></i><p>Live Map Hari Ini</p></a></li>@endif
                    @if($can('leaves')||$can('payrolls')||$can('reports')||$can('admin_profile'))<li class="nav-header">CUTI & PAYROLL</li>@endif
                    @if($can('leaves'))<li class="nav-item"><a href="/admin/leaves" class="nav-link {{ request()->is('admin/leaves*') ? 'active' : '' }}"><i class="nav-icon fas fa-envelope"></i><p>Pengajuan Cuti</p></a></li>@endif
<<<<<<< HEAD
                    @if($can('payrolls'))<li class="nav-item"><a href="/admin/company-settings" class="nav-link {{ request()->is('admin/company-settings*') ? 'active' : '' }}"><i class="nav-icon fas fa-building"></i><p>Perusahaan (Logo)</p></a></li>@endif
                    @if($can('payrolls'))<li class="nav-item"><a href="/admin/deduction-types" class="nav-link {{ request()->is('admin/deduction-types*') ? 'active' : '' }}"><i class="nav-icon fas fa-minus-circle"></i><p>Master Potongan</p></a></li>@endif
=======
>>>>>>> 03b750586559a20aacd64af62893c95988533e04
                    @if($can('payrolls'))<li class="nav-item"><a href="/admin/payrolls" class="nav-link {{ request()->is('admin/payrolls*') || request()->is('admin/payroll-detail*') ? 'active' : '' }}"><i class="nav-icon fas fa-money-bill"></i><p>Periode Gaji</p></a></li>@endif
                    @if($can('reports'))<li class="nav-item"><a href="/admin/reports" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-excel"></i><p>Laporan & Export</p></a></li>@endif

                    @if($can('employee_home')||$can('employee_history')||$can('employee_leaves')||$can('employee_leaves_create')||$can('employee_payslip')||$can('employee_menu')||$can('employee_profile'))<li class="nav-header">ABSENSI SAYA (KARYAWAN)</li>@endif
                    @if($can('employee_home'))<li class="nav-item"><a href="/employee/home" class="nav-link {{ request()->is('employee/home*') ? 'active' : '' }}"><i class="nav-icon fas fa-fingerprint"></i><p>Absen Hari Ini</p></a></li>@endif
                    @if($can('employee_history'))<li class="nav-item"><a href="/employee/history" class="nav-link {{ request()->is('employee/history*') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-check"></i><p>Riwayat Absen Saya</p></a></li>@endif
                    @if($can('employee_leaves'))<li class="nav-item"><a href="/employee/leaves" class="nav-link {{ request()->is('employee/leaves*') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-plus"></i><p>Cuti / Izin Saya</p></a></li>@endif
                    @if($can('employee_leaves_create'))<li class="nav-item"><a href="/employee/leaves/create" class="nav-link {{ request()->is('employee/leaves/create*') ? 'active' : '' }}"><i class="nav-icon fas fa-plus-circle"></i><p>Ajukan Cuti</p></a></li>@endif
                    @if($can('employee_payslip'))<li class="nav-item"><a href="/employee/payslip" class="nav-link {{ request()->is('employee/payslip*') ? 'active' : '' }}"><i class="nav-icon fas fa-wallet"></i><p>Slip Gaji Saya</p></a></li>@endif
                    @if($can('employee_menu'))<li class="nav-item"><a href="/employee/menu" class="nav-link {{ request()->is('employee/menu*') ? 'active' : '' }}"><i class="nav-icon fas fa-th-large"></i><p>Mobile Menu</p></a></li>@endif
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
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script>
    // Hilangkan alert DataTables warning (jika tidak ada data, jangan tampil alert)
    $.fn.dataTable.ext.errMode = 'none';
    // Auto-inisialisasi semua tabel dengan class .datatable
    $(function(){
        $('.datatable').each(function(){
            if ( $.fn.DataTable.isDataTable(this) ) return;
            // Jika tbody hanya berisi placeholder colspan (empty forelse), kosongkan agar DataTables tidak warning kolom mismatch
            var $tbody = $(this).find('tbody');
            if ($tbody.find('tr td[colspan]').length > 0) {
                $tbody.empty();
            }
            $(this).DataTable({
                responsive: true,
                autoWidth: false,
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Tidak ada data yang cocok',
                    emptyTable: 'Tidak ada data',
                    paginate: { next: '›', previous: '‹' }
                },
                dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                     "<'row'<'col-12'tr>>" +
                     "<'row'<'col-sm-5'i><'col-sm-7'p>>"
            });
        });
        // Fallback: jika CDN i18n gagal, pastikan tetap tidak alert
        $(document).on('error.dt', function(e, settings, techNote, message){
            console.warn('DataTables:', message);
            return false;
        });
    });
</script>
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
