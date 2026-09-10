<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuSetting;
use App\Models\User;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuSettingController extends Controller
{
    public function index(Request $request)
    {
        $allRoles = $this->getAllRoles();
        // Role yang akses mobile (checkbox di Akses Mobile)
        $mobileMenuId = Menu::where('key','akses_mobile')->value('id');
        $mobileRoles = $mobileMenuId ? MenuSetting::where('menu_id',$mobileMenuId)->where('is_allowed',true)->pluck('role')->map(fn($r)=>strtolower($r))->toArray() : [];

        // Untuk matrix Setting Menu, sembunyikan role yang sudah akses mobile (hanya mobile, tidak setting admin)
        $rolesForMatrix = array_values(array_diff($allRoles, $mobileRoles));
        if (empty($rolesForMatrix)) $rolesForMatrix = $allRoles; // fallback jika semua mobile

        $selectedRole = $request->query('role', $rolesForMatrix[0] ?? $allRoles[0] ?? 'staff');
        if (!in_array($selectedRole, $rolesForMatrix) && !in_array($selectedRole, $allRoles)) $selectedRole = $rolesForMatrix[0] ?? $allRoles[0] ?? 'staff';
        // jika selectedRole ternyata mobile, paksa ke non-mobile pertama
        if (in_array($selectedRole, $mobileRoles) && !empty($rolesForMatrix)) $selectedRole = $rolesForMatrix[0];

        $menus = Menu::where('key','!=','akses_mobile')->orderBy('sort_order')->get()->groupBy('group');
        // load settings for selected role
        $settings = MenuSetting::where('role', $selectedRole)->pluck('is_allowed','menu_id');

        // untuk CRUD menu: list semua menu flat (kecuali akses_mobile agar tidak dihapus sembarang)
        $allMenusFlat = Menu::orderBy('sort_order')->get();
        $mobileSettings = $mobileMenuId ? MenuSetting::where('menu_id',$mobileMenuId)->pluck('is_allowed','role')->toArray() : [];
        // untuk kompatibilitas view lama, $roles = matrix roles
        $roles = $rolesForMatrix;

        return view('admin.menu-settings.index', compact('roles','allRoles','rolesForMatrix','selectedRole','menus','settings','allMenusFlat','mobileRoles','mobileSettings','mobileMenuId'));
    }

    private function getAllRoles(): array
    {
        // role dinamis dari jabatan (slug) + yang sudah ada di users + default 3
        $userRoles = User::distinct()->pluck('role')->filter()->map(fn($r)=>strtolower(trim($r)))->toArray();
        $positionRoles = Position::all()->map(fn($p)=> strtolower(Str::slug($p->name, '_')))->filter()->toArray();
        $settingsRoles = MenuSetting::distinct()->pluck('role')->filter()->map(fn($r)=>strtolower(trim($r)))->toArray();
        $merged = array_unique(array_merge(['hrd','supervisor','staff'], $userRoles, $positionRoles, $settingsRoles));
        sort($merged);
        // pastikan hrd di depan
        $merged = array_unique(array_merge(['hrd','supervisor','staff'], $merged));
        return array_values(array_filter($merged));
    }

    public function update(Request $request)
    {
        $request->validate([
            'role' => 'required|string|max:50',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:0,1',
        ]);

        $role = $request->role;
        $permissions = $request->input('permissions', []); // menu_id => 1/0

        // Semua menu harus ada entry, jika tidak ada di request berarti 0 (tidak dicentang = tidak boleh)
        $allMenus = Menu::pluck('id');
        foreach ($allMenus as $menuId) {
            $isAllowed = isset($permissions[$menuId]) && $permissions[$menuId] == '1';
            MenuSetting::updateOrCreate(
                ['role' => $role, 'menu_id' => $menuId],
                ['is_allowed' => $isAllowed]
            );
        }

        return back()->with('success', 'Hak akses menu untuk role '.strtoupper($role).' berhasil disimpan. Menu di sidebar akan menyesuaikan.');
    }

    public function updateMobile(Request $request)
    {
        $selected = $request->input('roles', []); // array role yang dicheck = akses mobile
        if (!is_array($selected)) $selected = [];
        $selected = array_map(fn($r)=>strtolower(trim($r)), $selected);
        $mobileMenuId = Menu::where('key','akses_mobile')->value('id');
        if (!$mobileMenuId) return back()->withErrors(['msg'=>'Menu akses_mobile tidak ditemukan']);
        $allRoles = $this->getAllRoles();
        foreach ($allRoles as $r) {
            $isAllowed = in_array(strtolower($r), $selected);
            MenuSetting::updateOrCreate(['role'=>$r,'menu_id'=>$mobileMenuId], ['is_allowed'=>$isAllowed]);
        }
        return back()->with('success','Akses Mobile disimpan. Role yang dicentang akan langsung ke /employee/home saat login (tidak muncul di Setting Menu matrix).');
    }

    public function reset(Request $request)
    {
        $role = strtolower(trim($request->query('role','staff')));
        MenuSetting::where('role',$role)->delete();
        $all = Menu::pluck('id','key');
        if ($role === 'hrd') {
            foreach ($all as $id) MenuSetting::create(['role'=>$role,'menu_id'=>$id,'is_allowed'=>true]);
        } elseif ($role === 'supervisor') {
            $allowed = ['dashboard','employees','employees_pending','employees_resigned','attendances','live_map','leaves','payrolls','reports','admin_profile','employee_home','employee_history','employee_leaves','employee_leaves_create','employee_payslip','employee_menu','employee_profile'];
            foreach ($all as $k=>$id) MenuSetting::create(['role'=>$role,'menu_id'=>$id,'is_allowed'=>in_array($k,$allowed)]);
        } else {
            // untuk role dinamis seperti manager_finance: default hanya dashboard + absensi saya (bisa diubah manual)
            // jika role mengandung hrd/manager → beri akses lebih, else staff-like
            if (str_contains($role, 'hrd') || str_contains($role, 'manager')) {
                $allowed = ['dashboard','employees','employees_pending','employees_resigned','attendances','live_map','leaves','payrolls','reports','admin_profile','employee_home','employee_history','employee_leaves','employee_leaves_create','employee_payslip','employee_menu','employee_profile'];
            } else {
                $allowed = ['dashboard','employee_home','employee_history','employee_leaves','employee_leaves_create','employee_payslip','employee_menu','employee_profile'];
            }
            foreach ($all as $k=>$id) MenuSetting::create(['role'=>$role,'menu_id'=>$id,'is_allowed'=>in_array($k,$allowed)]);
        }
        return back()->with('success','Reset ke default untuk role '.strtoupper($role).' berhasil.');
    }

    // CRUD Menu (tabel menus) — tidak terbatas, bisa tambah/update/delete
    public function storeMenu(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:50|unique:menus,key|regex:/^[a-z0-9_]+$/',
            'name' => 'required|string|max:100',
            'group' => 'nullable|string|max:50',
            'route_name' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);
        $menu = Menu::create([
            'key' => strtolower(trim($request->key)),
            'name' => $request->name,
            'group' => $request->group,
            'route_name' => $request->route_name,
            'url' => $request->url,
            'icon' => $request->icon ?? 'fa-circle',
            'sort_order' => $request->sort_order ?? 99,
            'is_active' => true,
        ]);
        // auto buat setting untuk semua role existing = false (HRD auto true)
        $roles = $this->getAllRoles();
        foreach ($roles as $r) {
            $isAllowed = $r === 'hrd' ? true : false;
            MenuSetting::firstOrCreate(['role'=>$r,'menu_id'=>$menu->id], ['is_allowed'=>$isAllowed]);
        }
        return back()->with('success','Menu "'.$menu->name.'" berhasil ditambahkan (key: '.$menu->key.'). Silakan setting hak akses per role.');
    }

    public function updateMenu(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'group' => 'nullable|string|max:50',
            'route_name' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);
        $menu->update($request->only(['name','group','route_name','url','icon','sort_order']));
        return back()->with('success','Menu "'.$menu->name.'" berhasil diupdate.');
    }

    public function destroyMenu(Menu $menu)
    {
        $name = $menu->name;
        $menu->delete(); // cascade hapus menu_settings
        return back()->with('success','Menu "'.$name.'" berhasil dihapus.');
    }
}
