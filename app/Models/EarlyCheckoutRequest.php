<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarlyCheckoutRequest extends Model
{
    protected $fillable = [
        'student_id', 'date', 'requested_time', 'reason', 'type', 'absence_category', 'status',
        'reviewed_by', 'reviewed_at', 'reviewer_note', 'checked_out_at',
    ];

    protected $casts = [
        'date'           => 'date',
        'reviewed_at'    => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool     { return $this->status === 'pending'; }
    public function isApproved(): bool    { return $this->status === 'approved'; }
    public function isRejected(): bool    { return $this->status === 'rejected'; }
    public function isDenganAbsen(): bool { return ($this->type ?? 'dengan_absen') === 'dengan_absen'; }
    public function isTanpaAbsen(): bool  { return $this->type === 'tanpa_absen'; }

    public function typeLabel(): string
    {
        if ($this->isTanpaAbsen()) {
            $cat = ucfirst($this->absence_category ?? 'izin');
            return "Tanpa Absen ({$cat})";
        }
        return "Dengan Absen (Hadir)";
    }

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
        return substr((string) $this->requested_time, 0, 5);
    }

    /** Check if there is an approved early-checkout with checkout required for a student today. */
    public static function approvedWithCheckoutToday(int $studentId): bool
    {
        return static::where('student_id', $studentId)
            ->whereDate('date', today())
            ->where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('type')->orWhere('type', 'dengan_absen');
            })
            ->exists();
    }

    /** Check if there is an approved early-checkout for a student today (any type). */
    public static function approvedToday(int $studentId): bool
    {
        return static::where('student_id', $studentId)
            ->whereDate('date', today())
            ->where('status', 'approved')
            ->exists();
    }
}
