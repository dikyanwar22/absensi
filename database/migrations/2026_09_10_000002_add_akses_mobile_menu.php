<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah menu Akses Mobile jika belum ada
        $exists = DB::table('menus')->where('key', 'akses_mobile')->exists();
        if (!$exists) {
            DB::table('menus')->insert([
                'key' => 'akses_mobile',
                'name' => 'Akses Mobile',
                'group' => 'AKSES',
                'route_name' => 'employee.home',
                'url' => '/employee/home',
                'icon' => 'fa-mobile-alt',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $menuId = DB::table('menus')->where('key', 'akses_mobile')->value('id');
        // Default: tidak ada role yang akses mobile (semua bisa ke admin), HRD bisa set nanti
        // Jika belum ada setting untuk akses_mobile, buat default false untuk semua role existing
        $roles = DB::table('users')->distinct()->pluck('role')->filter()->toArray();
        $roles = array_unique(array_merge($roles, ['hrd','supervisor','staff']));
        foreach ($roles as $r) {
            $existsSetting = DB::table('menu_settings')->where('role', $r)->where('menu_id', $menuId)->exists();
            if (!$existsSetting) {
                DB::table('menu_settings')->insert([
                    'role' => $r,
                    'menu_id' => $menuId,
                    'is_allowed' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        // Juga untuk role dari posisi
        $posRoles = DB::table('positions')->get()->map(fn($p)=> strtolower(str_replace(' ', '_', strtolower($p->name))))->unique();
        foreach ($posRoles as $r) {
            if (!in_array($r, $roles)) {
                $existsSetting = DB::table('menu_settings')->where('role', $r)->where('menu_id', $menuId)->exists();
                if (!$existsSetting) {
                    DB::table('menu_settings')->insert([
                        'role' => $r,
                        'menu_id' => $menuId,
                        'is_allowed' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $id = DB::table('menus')->where('key', 'akses_mobile')->value('id');
        if ($id) {
            DB::table('menu_settings')->where('menu_id', $id)->delete();
            DB::table('menus')->where('id', $id)->delete();
        }
    }
};
