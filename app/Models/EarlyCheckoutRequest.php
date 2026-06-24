<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarlyCheckoutRequest extends Model
{
    protected $fillable = [
        'student_id', 'date', 'requested_time', 'reason', 'status',
        'reviewed_by', 'reviewed_at', 'reviewer_note',
    ];

    protected $casts = [
        'date'        => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-gray-100 text-gray-700',
        };
    }

    public function requestedTimeFormatted(): string
    {
        return substr($this->requested_time, 0, 5);
    }

    /** Check if there is an approved early-checkout for a student today. */
    public static function approvedToday(int $studentId): bool
    {
        return static::where('student_id', $studentId)
            ->whereDate('date', today())
            ->where('status', 'approved')
            ->exists();
    }
}
