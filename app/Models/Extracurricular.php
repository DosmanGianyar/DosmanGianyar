<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Extracurricular extends Model
{
    protected $fillable = [
        'name', 'description', 'logo', 'pembina_id', 'max_members', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'max_members' => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ExtracurricularMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(ExtracurricularMember::class)->where('status', 'active');
    }

    public function pendingMembers(): HasMany
    {
        return $this->hasMany(ExtracurricularMember::class)
            ->whereIn('status', ['pending_join', 'pending_leave']);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExtracurricularSession::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function activeMemberCount(): int
    {
        return $this->activeMembers()->count();
    }

    public function isFull(): bool
    {
        return $this->max_members !== null
            && $this->activeMemberCount() >= $this->max_members;
    }

    public function memberStatusFor(int $userId): ?string
    {
        $member = $this->members()->where('user_id', $userId)->first();
        return $member?->status;
    }

    public function memberRoleFor(int $userId): ?string
    {
        $member = $this->activeMembers()->where('user_id', $userId)->first();
        return $member?->role;
    }
}
