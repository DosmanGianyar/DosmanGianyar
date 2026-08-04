<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'target', 'target_class_ids', 'is_pinned', 'published_at', 'author_id',
    ];

    protected $casts = [
        'is_pinned'        => 'boolean',
        'published_at'     => 'datetime',
        'target_class_ids' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
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
        return $this->published_at !== null && $this->published_at->lte(now());
    }
}
