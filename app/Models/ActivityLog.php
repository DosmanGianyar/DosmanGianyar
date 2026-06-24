<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public    $timestamps = false;
    protected $fillable   = ['user_id', 'action', 'subject_type', 'subject_id', 'changes', 'ip_address', 'user_agent', 'created_at'];
    protected $casts      = ['changes' => 'array', 'created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?Model $subject = null, ?array $changes = null): void
    {
        static::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'changes'      => $changes,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'created_at'   => now(),
        ]);
    }
}
