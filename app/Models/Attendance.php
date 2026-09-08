<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id','date','shift_id','check_in','check_out',
        'lat_in','lng_in','accuracy_in','address_in',
        'lat_out','lng_out','accuracy_out','address_out',
        'distance_in_meter','distance_out_meter','photo_in','photo_out',
        'status','late_minutes','overtime_hours','is_fake_gps','ip_address','user_agent'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_fake_gps' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
}
