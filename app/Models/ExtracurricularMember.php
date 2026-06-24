<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracurricularMember extends Model
{
    protected $fillable = [
        'extracurricular_id', 'user_id', 'role', 'status', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending_join', 'pending_leave']);
    }

    public function scopeKetua(Builder $query): Builder
    {
        return $query->where('role', 'ketua')->where('status', 'active');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending_join'  => 'Menunggu Persetujuan Bergabung',
            'active'        => 'Anggota Aktif',
            'pending_leave' => 'Mengajukan Keluar',
            default         => $this->status,
        };
    }

    public function roleLabel(): string
    {
        return match($this->role) {
            'ketua'  => 'Ketua',
            'member' => 'Anggota',
            default  => $this->role,
        };
    }
}
