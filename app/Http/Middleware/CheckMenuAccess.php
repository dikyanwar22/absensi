<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuSetting;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Cek apakah role user berhak akses menu berdasarkan route name
     */
    public function handle(Request $request, Closure $next, ?string $menuKey = null): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // Jika menuKey tidak diberikan, coba tebak dari route name
        if (!$menuKey) {
            $routeName = $request->route() ? $request->route()->getName() : null;
            if ($routeName) {
                $menu = Menu::where('route_name', $routeName)->first();
                if (!$menu) {
                    // coba prefix match: mis admin.departments.create → departments
                    $menus = Menu::all();
                    foreach ($menus as $m) {
                        if ($m->route_name && (\Illuminate\Support\Str::startsWith($routeName, $m->route_name) || \Illuminate\Support\Str::startsWith($m->route_name, $routeName))) {
                            $menu = $m;
                            break;
                        }
                        // juga cek prefix tanpa .index/.create/.edit
                        $base = explode('.', $m->route_name)[0] . '.' . (explode('.', $m->route_name)[1] ?? '');
                        if (\Illuminate\Support\Str::startsWith($routeName, $base)) {
                            $menu = $m;
                            break;
                        }
                    }
                }
                if (!$menu) {
                    // coba match by url prefix
                    $menu = Menu::where('url', $request->path())->first();
                    if (!$menu) {
                        // url prefix
                        $menus = Menu::all();
                        foreach ($menus as $m) {
                            if ($m->url && \Illuminate\Support\Str::startsWith('/'.$request->path(), $m->url)) {
                                $menu = $m;
                                break;
                            }
                        }
                    }
                }
                $menuKey = $menu ? $menu->key : null;
            }
        }

        if ($menuKey) {
            $role = $user->role ?? 'staff';
            if (!MenuSetting::canAccess($role, $menuKey)) {
                abort(403, 'Akses menu "'.$menuKey.'" ditolak untuk role '.strtoupper($role).'. Hubungi HRD untuk setting hak akses.');
            }
        }

        return $next($request);
    }
}
