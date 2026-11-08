<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentFeatureSeen extends Model
{
    protected $fillable = [
        'parent_id',
        'student_id',
        'feature',
        'last_seen_at',
        'last_seen_id',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'last_seen_id' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
