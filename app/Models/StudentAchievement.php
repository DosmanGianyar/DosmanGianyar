<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAchievement extends Model
{
    protected $fillable = [
        'student_id', 'category_id', 'title', 'description',
        'achievement_date', 'level', 'rank', 'photo', 'certificate',
        'status', 'verified_by', 'verified_at', 'rejection_reason',
    ];

    protected $casts = [
        'achievement_date' => 'date',
        'verified_at'      => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AchievementCategory::class, 'category_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function levelLabel(): string
    {
        return match ($this->level) {
            'sekolah'       => 'Sekolah',
            'kabupaten'     => 'Kabupaten/Kota',
            'provinsi'      => 'Provinsi',
            'nasional'      => 'Nasional',
            'internasional' => 'Internasional',
            default         => ucfirst($this->level),
        };
    }

    public function levelColor(): string
    {
        return match ($this->level) {
            'sekolah'       => 'gray',
            'kabupaten'     => 'info',
            'provinsi'      => 'warning',
            'nasional'      => 'success',
            'internasional' => 'danger',
            default         => 'gray',
        };
    }

    public function levelBadgeClass(): string
    {
        return match ($this->level) {
            'sekolah'       => 'bg-gray-100 text-gray-700',
            'kabupaten'     => 'bg-blue-100 text-blue-700',
            'provinsi'      => 'bg-yellow-100 text-yellow-700',
            'nasional'      => 'bg-green-100 text-green-700',
            'internasional' => 'bg-red-100 text-red-700',
            default         => 'bg-gray-100 text-gray-700',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => 'Menunggu',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default    => 'warning',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-yellow-100 text-yellow-700',
        };
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function certificateUrl(): ?string
    {
        return $this->certificate ? asset('storage/' . $this->certificate) : null;
    }
}
