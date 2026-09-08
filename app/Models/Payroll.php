<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = ['period','start_date','end_date','status','total_employees','total_amount','created_by','locked_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'locked_at' => 'datetime',
    ];

    public function details(): HasMany { return $this->hasMany(PayrollDetail::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
