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
        return MenuSetting::canAccess($role, $menuKey);
    }

    public static function canRole(string $role, string $menuKey): bool
    {
        return MenuSetting::canAccess($role, $menuKey);
    }
}
