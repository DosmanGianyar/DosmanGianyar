<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkLog extends Model
{
    protected $fillable = [
        'student_id', 'counselor_id', 'coaching_note', 'point_at_time', 'is_auto', 'date',
    ];

    protected function casts(): array
    {
        return [
            'date'    => 'date',
            'is_auto' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
