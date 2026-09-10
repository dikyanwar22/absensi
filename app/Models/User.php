<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nik',
        'email',
        'role',
        'status_account',
        'password',
        'notifications_read_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'notifications_read_at' => 'datetime',
        'status_account' => 'integer',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function isHRD(): bool
    {
        $r = strtolower($this->role ?? '');
        return $r === 'hrd' || str_contains($r, 'hrd') || str_contains($r, 'manager') || $r === 'manajer_finance' || $r === 'manager_finance';
    }

    public function isSupervisor(): bool
    {
        $r = strtolower($this->role ?? '');
        return $r === 'supervisor' || str_contains($r, 'supervisor') || str_contains($r, 'spv');
    }

    public function hasMenuAccess(string $menuKey): bool
    {
        return \App\Models\MenuSetting::canAccess($this->role ?? 'staff', $menuKey);
    }

    /**
     * Label role elegan untuk UI: OFFICE_BOY -> OFFICE BOY, manager_finance -> MANAGER FINANCE
     * Logic check tetap pakai ->role mentah (slug dengan underscore)
     */
    public function getDisplayRoleAttribute(): string
    {
        return strtoupper(str_replace('_', ' ', $this->role ?? 'STAFF'));
    }

    /**
     * Versi Title Case: office_boy -> Office Boy (untuk ucfirst konteks)
     */
    public function getRoleLabelAttribute(): string
    {
        return \Illuminate\Support\Str::headline($this->role ?? 'staff');
    }
}
