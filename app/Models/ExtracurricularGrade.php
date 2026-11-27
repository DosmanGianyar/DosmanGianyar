<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracurricularGrade extends Model
{
    public const PREDICATES = [
        'A' => 'Sangat Baik',
        'B' => 'Baik',
        'C' => 'Cukup',
        'D' => 'Kurang',
    ];

    protected $fillable = [
        'extracurricular_id',
        'user_id',
        'academic_year',
        'semester',
        'predicate',
        'description',
        'graded_by',
    ];

    protected $casts = [
        'semester' => 'integer',
    ];

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function predicateLabel(): string
    {
        return self::labelFor($this->predicate) ?? $this->predicate;
    }

    public static function labelFor(?string $predicate): ?string
    {
        if (! $predicate) {
            return null;
        }

        return self::PREDICATES[$predicate] ?? $predicate;
    }

    public static function defaultDescription(string $extraName, string $predicate): string
    {
        return match ($predicate) {
            'A' => "Sangat aktif dan menunjukkan prestasi serta kedisiplinan yang sangat baik dalam kegiatan ekstrakurikuler {$extraName}.",
            'B' => "Aktif mengikuti kegiatan dan menunjukkan sikap serta keterampilan yang baik dalam ekstrakurikuler {$extraName}.",
            'C' => "Cukup aktif mengikuti kegiatan ekstrakurikuler {$extraName}, perlu meningkatkan konsistensi kehadiran dan partisipasi.",
            'D' => "Kurang aktif mengikuti kegiatan ekstrakurikuler {$extraName}, perlu pembinaan dan peningkatan kehadiran.",
            default => '',
        };
    }
}
