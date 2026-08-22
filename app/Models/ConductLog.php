<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConductLog extends Model
{
    protected $fillable = [
        'student_id', 'teacher_id', 'type', 'category_id', 'photo', 'note',
        'description', 'severity',
        'prestasi_type', 'lomba_name', 'lomba_level', 'lomba_rank',
        'is_self_reported', 'status', 'verified_at', 'verifier_id',
    ];

    protected $casts = [
        'is_self_reported' => 'boolean',
        'verified_at'      => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ConductCategory::class, 'category_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function isPrestasi(): bool
    {
        $t = $this->category?->type ?? $this->type;
        return in_array($t, ['prestasi', 'positif']);
    }

    public function isPelanggaran(): bool
    {
        $t = $this->category?->type ?? $this->type;
        return $t === 'pelanggaran';
    }

    public function getParsedTitleAttribute(): string
    {
        if ($this->category && !str_starts_with($this->category->name, '__sistem__')) {
            return $this->category->name;
        }

        if (!empty($this->note) && preg_match('/^\[(.*?)\]\s*(.*)$/s', $this->note, $matches)) {
            $t = trim($matches[1]);
            return ucfirst($t);
        }

        if ($this->description) {
            return $this->description;
        }

        if ($this->lomba_name) {
            return 'Lomba: ' . $this->lomba_name;
        }

        return $this->isPrestasi() ? 'Catatan Apresiasi' : 'Catatan Kedisiplinan';
    }

    public function getParsedDescriptionAttribute(): ?string
    {
        if (!empty($this->note)) {
            if (preg_match('/^\[(.*?)\]\s*(.*)$/s', $this->note, $matches)) {
                return !empty($matches[2]) ? $matches[2] : null;
            }
            return $this->note;
        }

        return $this->description;
    }

    public function displayCategoryName(): string
    {
        return $this->getParsedTitleAttribute();
    }
}
