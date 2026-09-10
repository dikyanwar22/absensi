<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['key','name','group','route_name','url','icon','sort_order','is_active'];

    public function settings(): HasMany
    {
        return $this->hasMany(MenuSetting::class);
    }
}
