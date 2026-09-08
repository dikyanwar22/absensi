<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        'user_id','department_id','position_id','shift_id','office_location_id',
        'employee_code','phone','address','join_date','employment_status',
        'contract_end_date','resign_date','bank_name','bank_account','bpjs_kes','bpjs_tk','is_active','photo'
    ];

    protected $casts = [
        'join_date' => 'date',
        'contract_end_date' => 'date',
        'resign_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
    public function officeLocation(): BelongsTo { return $this->belongsTo(OfficeLocation::class, 'office_location_id'); }
}
