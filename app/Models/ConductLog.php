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
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ConductCategory::class, 'category_id');
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

    public function displayCategoryName(): string
    {
        return $this->category?->name 
            ?? $this->description 
            ?? ($this->lomba_name ? 'Lomba: ' . $this->lomba_name : null)
            ?? $this->note 
            ?? ($this->isPrestasi() ? 'Catatan Positif' : 'Catatan Negatif');
    }
}
