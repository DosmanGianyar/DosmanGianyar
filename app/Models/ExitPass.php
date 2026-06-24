<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitPass extends Model
{
    protected $fillable = [
        'student_id', 'reason', 'reason_detail', 'out_time', 'in_time', 'status',
    ];

    protected function casts(): array
    {
        return [
            'out_time' => 'datetime',
            'in_time'  => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->in_time) return null;
        return (int) $this->out_time->diffInMinutes($this->in_time);
    }

    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'toilet' => 'Toilet',
            'uks'    => 'UKS',
            default  => $this->reason_detail ?? 'Lainnya',
        };
    }
}
