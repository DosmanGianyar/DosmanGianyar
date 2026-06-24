<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = ['user_id', 'title', 'body', 'type', 'url', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function iconClass(): string
    {
        return match ($this->type) {
            'success' => 'bg-green-100 text-green-600',
            'warning' => 'bg-orange-100 text-orange-600',
            default   => 'bg-blue-100 text-blue-600',
        };
    }
}
