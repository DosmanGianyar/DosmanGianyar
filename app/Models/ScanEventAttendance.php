<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanEventAttendance extends Model
{
    protected $fillable = ['scan_event_id', 'student_id', 'scanned_at', 'scanned_by'];

    protected $casts = ['scanned_at' => 'datetime'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(ScanEvent::class, 'scan_event_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
