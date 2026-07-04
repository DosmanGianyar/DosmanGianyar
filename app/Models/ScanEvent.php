<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScanEvent extends Model
{
    protected $fillable = ['title', 'date', 'location', 'description', 'is_active', 'created_by'];

    protected $casts = ['date' => 'date', 'is_active' => 'boolean'];

    public function attendances(): HasMany
    {
        return $this->hasMany(ScanEventAttendance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
