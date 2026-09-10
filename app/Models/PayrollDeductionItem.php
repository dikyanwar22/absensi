<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDeductionItem extends Model
{
    protected $fillable = ['payroll_detail_id', 'deduction_type_id', 'name', 'amount'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function detail(): BelongsTo { return $this->belongsTo(PayrollDetail::class, 'payroll_detail_id'); }
    public function deductionType(): BelongsTo { return $this->belongsTo(DeductionType::class); }
}
