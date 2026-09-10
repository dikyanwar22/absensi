<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeductionType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'default_amount', 'is_active', 'description', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
        'default_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(PayrollDeductionItem::class); }

    // scope aktif saja yang auto terpotong saat generate
    public function scopeActive($q) { return $q->where('is_active', true); }
}
