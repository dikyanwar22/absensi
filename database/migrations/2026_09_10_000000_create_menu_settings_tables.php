<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel master menu (daftar semua menu admin & employee)
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // mis: dashboard, departments, employees
            $table->string('name'); // label tampil
            $table->string('group')->nullable(); // MASTER DATA, KARYAWAN, ABSENSI, dll
            $table->string('route_name')->nullable(); // route name untuk cek
            $table->string('url')->nullable(); // url path
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel setting hak akses per role
        Schema::create('menu_settings', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // hrd, supervisor, staff
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->boolean('is_allowed')->default(false);
            $table->timestamps();
            $table->unique(['role', 'menu_id']);
        });

        // Seed menus
        $menus = [
            // group, key, name, route, icon, sort
            ['group'=>'','key'=>'dashboard','name'=>'Dashboard','route'=>'admin.dashboard','url'=>'/admin/dashboard','icon'=>'fa-tachometer-alt','sort'=>10],
            ['group'=>'MASTER DATA','key'=>'departments','name'=>'Departemen','route'=>'admin.departments.index','url'=>'/admin/departments','icon'=>'fa-building','sort'=>20],
            ['group'=>'MASTER DATA','key'=>'positions','name'=>'Jabatan','route'=>'admin.positions.index','url'=>'/admin/positions','icon'=>'fa-briefcase','sort'=>21],
            ['group'=>'MASTER DATA','key'=>'shifts','name'=>'Shift Kerja','route'=>'admin.shifts.index','url'=>'/admin/shifts','icon'=>'fa-clock','sort'=>22],
            ['group'=>'MASTER DATA','key'=>'office_locations','name'=>'Lokasi Kantor','route'=>'admin.office-locations.index','url'=>'/admin/office-locations','icon'=>'fa-map-marker-alt','sort'=>23],
            ['group'=>'KARYAWAN','key'=>'employees','name'=>'Data Karyawan','route'=>'admin.employees.index','url'=>'/admin/employees','icon'=>'fa-users','sort'=>30],
            ['group'=>'KARYAWAN','key'=>'employees_pending','name'=>'Akun Pending','route'=>'admin.employees.pending','url'=>'/admin/employees/pending','icon'=>'fa-user-clock','sort'=>31],
            ['group'=>'KARYAWAN','key'=>'employees_resigned','name'=>'Resign / Cut-Off','route'=>'admin.employees.resigned','url'=>'/admin/employees/resigned','icon'=>'fa-user-times','sort'=>32],
            ['group'=>'KARYAWAN','key'=>'menu_settings','name'=>'Setting Menu','route'=>'admin.menu-settings.index','url'=>'/admin/menu-settings','icon'=>'fa-cogs','sort'=>33],
            ['group'=>'ABSENSI','key'=>'attendances','name'=>'Harian','route'=>'admin.attendances.index','url'=>'/admin/attendances','icon'=>'fa-calendar-check','sort'=>40],
            ['group'=>'ABSENSI','key'=>'live_map','name'=>'Live Map Hari Ini','route'=>'admin.attendances.live-map','url'=>'/admin/attendances/live-map','icon'=>'fa-map','sort'=>41],
            ['group'=>'CUTI & PAYROLL','key'=>'leaves','name'=>'Pengajuan Cuti','route'=>'admin.leaves.index','url'=>'/admin/leaves','icon'=>'fa-envelope','sort'=>50],
            ['group'=>'CUTI & PAYROLL','key'=>'payrolls','name'=>'Periode Gaji','route'=>'admin.payrolls.index','url'=>'/admin/payrolls','icon'=>'fa-money-bill','sort'=>51],
            ['group'=>'CUTI & PAYROLL','key'=>'reports','name'=>'Laporan & Export','route'=>'admin.reports.index','url'=>'/admin/reports','icon'=>'fa-file-excel','sort'=>52],
            ['group'=>'CUTI & PAYROLL','key'=>'admin_profile','name'=>'Profile (Admin)','route'=>'admin.profile.index','url'=>'/admin/profile','icon'=>'fa-user-circle','sort'=>53],
            // ABSENSI SAYA (employee)
            ['group'=>'ABSENSI SAYA','key'=>'employee_home','name'=>'Absen Hari Ini','route'=>'employee.home','url'=>'/employee/home','icon'=>'fa-fingerprint','sort'=>60],
            ['group'=>'ABSENSI SAYA','key'=>'employee_history','name'=>'Riwayat Absen Saya','route'=>'employee.history','url'=>'/employee/history','icon'=>'fa-calendar-check','sort'=>61],
            ['group'=>'ABSENSI SAYA','key'=>'employee_leaves','name'=>'Cuti / Izin Saya','route'=>'employee.leaves.index','url'=>'/employee/leaves','icon'=>'fa-calendar-plus','sort'=>62],
            ['group'=>'ABSENSI SAYA','key'=>'employee_leaves_create','name'=>'Ajukan Cuti','route'=>'employee.leaves.create','url'=>'/employee/leaves/create','icon'=>'fa-plus-circle','sort'=>63],
            ['group'=>'ABSENSI SAYA','key'=>'employee_payslip','name'=>'Slip Gaji Saya','route'=>'employee.payslip','url'=>'/employee/payslip','icon'=>'fa-wallet','sort'=>64],
            ['group'=>'ABSENSI SAYA','key'=>'employee_menu','name'=>'Menu Mobile Saya','route'=>'employee.menu','url'=>'/employee/menu','icon'=>'fa-th-large','sort'=>65],
            ['group'=>'ABSENSI SAYA','key'=>'employee_profile','name'=>'Profil Saya','route'=>'employee.profile','url'=>'/employee/profile','icon'=>'fa-id-badge','sort'=>66],
        ];
        foreach ($menus as $m) {
            DB::table('menus')->insert([
                'key'=>$m['key'],'name'=>$m['name'],'group'=>$m['group'],'route_name'=>$m['route'],'url'=>$m['url'],'icon'=>$m['icon'],'sort_order'=>$m['sort'],'is_active'=>true,'created_at'=>now(),'updated_at'=>now()
            ]);
        }

        // Default permissions
        $allMenuIds = DB::table('menus')->pluck('id','key'); // key => id
        $roles = ['hrd','supervisor','staff'];
        // HRD: semua
        foreach ($allMenuIds as $key=>$id) {
            DB::table('menu_settings')->insert(['role'=>'hrd','menu_id'=>$id,'is_allowed'=>true,'created_at'=>now(),'updated_at'=>now()]);
        }
        // Supervisor: tidak master data office_locations & setting menu, tapi bisa lainnya
        $supervisorAllowed = ['dashboard','employees','employees_pending','employees_resigned','attendances','live_map','leaves','payrolls','reports','admin_profile','employee_home','employee_history','employee_leaves','employee_leaves_create','employee_payslip','employee_menu','employee_profile'];
        foreach ($supervisorAllowed as $k) {
            if(isset($allMenuIds[$k])){
                // sudah ada hrd, perlu update atau insert ignore; kita sudah insert hrd, sekarang supervisor
                DB::table('menu_settings')->insert(['role'=>'supervisor','menu_id'=>$allMenuIds[$k],'is_allowed'=>true,'created_at'=>now(),'updated_at'=>now()]);
            }
        }
        // supervisor denied untuk master data & setting
        $supervisorDenied = ['departments','positions','shifts','office_locations','menu_settings'];
        foreach ($supervisorDenied as $k) {
            if(isset($allMenuIds[$k])){
                DB::table('menu_settings')->insert(['role'=>'supervisor','menu_id'=>$allMenuIds[$k],'is_allowed'=>false,'created_at'=>now(),'updated_at'=>now()]);
            }
        }
        // Staff: hanya dashboard + absensi saya + pengajuan cuti (tidak bisa master, karyawan, payroll admin)
        $staffAllowed = ['dashboard','employee_home','employee_history','employee_leaves','employee_leaves_create','employee_payslip','employee_menu','employee_profile'];
        foreach ($staffAllowed as $k) {
            if(isset($allMenuIds[$k])){
                DB::table('menu_settings')->insert(['role'=>'staff','menu_id'=>$allMenuIds[$k],'is_allowed'=>true,'created_at'=>now(),'updated_at'=>now()]);
            }
        }
        $staffDenied = array_diff(array_keys($allMenuIds->toArray()), $staffAllowed);
        foreach ($staffDenied as $k) {
            if(isset($allMenuIds[$k])){
                // cek sudah ada?
                $exists = DB::table('menu_settings')->where('role','staff')->where('menu_id',$allMenuIds[$k])->exists();
                if(!$exists){
                    DB::table('menu_settings')->insert(['role'=>'staff','menu_id'=>$allMenuIds[$k],'is_allowed'=>false,'created_at'=>now(),'updated_at'=>now()]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_settings');
        Schema::dropIfExists('menus');
    }
};
