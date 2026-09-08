<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $fillable = [
        'user_id','supervisor_id','backup_user_id','leave_type_id','start_date','end_date','total_days','reason','document_path',
        'status_supervisor','status_hrd','final_status','supervisor_note','hrd_note',
        'approved_by_supervisor_id','approved_by_hrd_id'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function supervisor(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function backupUser(): BelongsTo { return $this->belongsTo(User::class, 'backup_user_id'); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
}
