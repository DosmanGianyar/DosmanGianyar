<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'image', 'target', 'target_class_ids', 'is_pinned',
        'is_active', 'show_as_modal', 'published_at', 'expires_at', 'author_id',
    ];

    protected $casts = [
        'is_pinned'        => 'boolean',
        'is_active'        => 'boolean',
        'show_as_modal'    => 'boolean',
        'published_at'     => 'datetime',
        'expires_at'       => 'datetime',
        'target_class_ids' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) return null;
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->image);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeActiveModal(Builder $query, string $role = 'siswa', ?int $classId = null): Builder
    {
        return $query->published()
            ->where('show_as_modal', true)
            ->forRole($role, $classId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at');
    }

    public function scopeForRole(Builder $query, string $role, ?int $classId = null): Builder
    {
        return $query->where(function ($q) use ($role, $classId) {
            $q->where('target', 'all')->orWhere('target', $role);
        })->where(function ($q) use ($classId) {
            $q->whereNull('target_class_ids')
              ->orWhereJsonLength('target_class_ids', 0);
            if ($classId) {
                $q->orWhereJsonContains('target_class_ids', $classId);
            }
        });
    }

    public function isPublished(): bool
    {
        return $this->is_active
            && $this->published_at !== null
            && $this->published_at->lte(now())
            && ($this->expires_at === null || $this->expires_at->gte(now()));
    }
}
