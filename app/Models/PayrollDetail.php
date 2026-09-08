<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    protected $fillable = [
        'payroll_id','user_id','basic_salary','allowances','overtime_pay','bonus','thr',
        'deductions','gross_salary','total_deduction','net_salary','attendance_summary','notes','slip_pdf_path'
    ];

    protected $casts = [
        'allowances' => 'array',
        'deductions' => 'array',
        'attendance_summary' => 'array',
    ];

    public function payroll(): BelongsTo { return $this->belongsTo(Payroll::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
