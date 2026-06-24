<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGrade extends Model
{
    protected $fillable = [
        'student_id', 'subject_id', 'score', 'type',
        'semester', 'academic_year', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'UH'  => 'Ulangan Harian',
            'UTS' => 'UTS',
            'UAS' => 'UAS',
            default => $this->type,
        };
    }

    public function scoreColor(): string
    {
        if ($this->score >= 80) return 'text-green-600';
        if ($this->score >= 65) return 'text-yellow-600';
        return 'text-red-600';
    }

    public static function currentAcademicYear(): string
    {
        $year = now()->month >= 7 ? now()->year : now()->year - 1;
        return $year . '/' . ($year + 1);
    }

    public static function currentSemester(): int
    {
        return now()->month >= 7 ? 1 : 2;
    }
}
