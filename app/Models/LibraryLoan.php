<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LibraryLoan extends Model
{
    protected $fillable = [
        'student_id',
        'manual_student_name',
        'manual_class_name',
        'phone_number',
        'book_title',
        'book_code',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'notes',
        'purpose',
        'created_by_user_id',
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'due_at'      => 'date',
        'returned_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getStudentNameAttribute(): string
    {
        return $this->student?->name ?? $this->manual_student_name ?? '—';
    }

    public function getClassNameAttribute(): string
    {
        return $this->student?->schoolClass?->name ?? $this->manual_class_name ?? '—';
    }

    public function getPhoneAttribute(): string
    {
        return $this->phone_number ?? $this->student?->phone ?? '—';
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'returned') {
            return false;
        }
        if ($this->status === 'overdue') {
            return true;
        }
        return $this->due_at && Carbon::now()->startOfDay()->gt($this->due_at);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'returned' => 'Sudah Dikembalikan',
            'overdue'  => 'Terlambat',
            default    => 'Sedang Dipinjam',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'returned' => 'success',
            'overdue'  => 'danger',
            default    => 'warning',
        };
    }
}
