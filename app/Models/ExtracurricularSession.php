<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtracurricularSession extends Model
{
    protected $fillable = [
        'extracurricular_id', 'title', 'session_date',
        'start_time', 'end_time', 'location', 'notes',
        'created_by', 'is_open',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'is_open'      => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ExtracurricularAttendance::class, 'session_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('session_date', '>=', today())->orderBy('session_date')->orderBy('start_time');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('session_date', '<', today())->orderByDesc('session_date');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function hadirCount(): int
    {
        return $this->attendances()->where('status', 'hadir')->count();
    }

    public function alpaCount(): int
    {
        return $this->attendances()->where('status', 'alpa')->count();
    }

    public function attendanceFor(int $userId): ?ExtracurricularAttendance
    {
        return $this->attendances()->where('user_id', $userId)->first();
    }
}
