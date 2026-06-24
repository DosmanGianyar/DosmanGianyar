<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeroomConsultation extends Model
{
    protected $fillable = [
        'student_id', 'teacher_id', 'class_id',
        'topic', 'student_note',
        'status', 'scheduled_date', 'conducted_date',
        'teacher_note', 'follow_up', 'cancelled_reason',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'conducted_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'   => 'Menunggu Konfirmasi',
            'scheduled' => 'Dijadwalkan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default     => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'pending'   => 'amber',
            'scheduled' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isScheduled(): bool  { return $this->status === 'scheduled'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
}
