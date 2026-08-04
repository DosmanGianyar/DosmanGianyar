<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Extracurricular extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'description',
        'pembina_id',
        'logo',
        'max_members',
        'is_active',
    ];

    // ─── Legacy & Filament Relations ──────────────────────────────────────────

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ExtracurricularMember::class, 'extracurricular_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->where('status', 'active');
    }

    public function pendingMembers(): HasMany
    {
        return $this->members()->whereIn('status', ['pending_join', 'pending_leave']);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExtracurricularSession::class, 'extracurricular_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ─── Multi-Pembina & Pengurus Relations ───────────────────────────────────

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'extracurricular_teachers', 'extracurricular_id', 'teacher_id')
            ->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'extracurricular_students', 'extracurricular_id', 'student_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function leaders(): BelongsToMany
    {
        return $this->students()->wherePivotIn('role', ['ketua', 'wakil_ketua']);
    }
}
