<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permit extends Model
{
    protected $fillable = [
        'student_id', 'approved_by', 'type', 'start_date', 'end_date',
        'reason', 'file', 'status', 'rejection_note',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }

    public function typeLabel(): string
    {
        return match($this->type) {
            'izin'       => 'Izin',
            'sakit'      => 'Sakit',
            'dispensasi' => 'Dispensasi',
            default      => ucfirst($this->type),
        };
    }

    public function typeBadgeClass(): string
    {
        return match($this->type) {
            'izin'       => 'bg-sky-100 text-sky-700',
            'sakit'      => 'bg-purple-100 text-purple-700',
            'dispensasi' => 'bg-orange-100 text-orange-700',
            default      => 'bg-gray-100 text-gray-700',
        };
    }

    public function coversDate(string $date): bool
    {
        return $this->isApproved()
            && $this->start_date->lte($date)
            && $this->end_date->gte($date);
    }
}
