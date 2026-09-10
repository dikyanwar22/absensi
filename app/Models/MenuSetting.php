<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuSetting extends Model
{
    protected $fillable = ['role','menu_id','is_allowed'];

    protected $casts = ['is_allowed'=>'boolean'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Cek apakah role boleh akses menu key
     */
    public static function canAccess(?string $role, string $menuKey): bool
    {
        if (!$role) return false;
        // HRD selalu boleh jika belum ada setting? Tapi kita sudah seed semua, jadi cek DB
        $menu = Menu::where('key', $menuKey)->first();
        if (!$menu) return false;
        $setting = static::where('role', $role)->where('menu_id', $menu->id)->first();
        if (!$setting) return false;
        return (bool) $setting->is_allowed;
    }
}
