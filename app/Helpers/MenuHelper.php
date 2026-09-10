<?php

namespace App\Helpers;

use App\Models\MenuSetting;
use Illuminate\Support\Facades\Cache;

class MenuHelper
{
    public static function can(string $menuKey): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        $role = $user->role ?? 'staff';
<<<<<<< HEAD
=======
        // HRD bypass? tetap cek setting agar bisa dimatikan via setting
>>>>>>> 03b750586559a20aacd64af62893c95988533e04
        return MenuSetting::canAccess($role, $menuKey);
    }

    public static function canRole(string $role, string $menuKey): bool
    {
        return MenuSetting::canAccess($role, $menuKey);
    }
}
