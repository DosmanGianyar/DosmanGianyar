<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Holiday extends Model
{
    protected $fillable = ['date', 'description', 'type', 'applies_to'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function schoolClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'holiday_class', 'holiday_id', 'school_class_id')
            ->withTimestamps();
    }

    /**
     * Is the given date an off-day for the given class?
     * Weekend = off, UNLESS a sekolah_khusus entry overrides it for this class.
     * Weekday = off only if a libur entry applies to this class.
     */
    public static function isOffDayFor(Carbon $date, ?int $classId): bool
    {
        if ($date->isWeekend()) {
            return ! static::specialSchoolDayExistsFor($date, $classId);
        }
        return static::holidayExistsFor($date, $classId);
    }

    public static function holidayExistsFor(Carbon $date, ?int $classId): bool
    {
        return static::whereDate('date', $date)
            ->where('type', 'libur')
            ->where(fn ($q) => $q
                ->where('applies_to', 'semua')
                ->orWhere(fn ($q2) => $q2
                    ->where('applies_to', 'kelas_tertentu')
                    ->whereHas('schoolClasses', fn ($q3) => $q3->where('school_classes.id', $classId))
                )
            )
            ->exists();
    }

    public static function specialSchoolDayExistsFor(Carbon $date, ?int $classId): bool
    {
        return static::whereDate('date', $date)
            ->where('type', 'sekolah_khusus')
            ->where(fn ($q) => $q
                ->where('applies_to', 'semua')
                ->orWhere(fn ($q2) => $q2
                    ->where('applies_to', 'kelas_tertentu')
                    ->whereHas('schoolClasses', fn ($q3) => $q3->where('school_classes.id', $classId))
                )
            )
            ->exists();
    }

    /** Date => true map of libur days in range for this class. */
    public static function getHolidayDates(Carbon $start, Carbon $end, ?int $classId): array
    {
        return static::whereBetween('date', [$start, $end])
            ->where('type', 'libur')
            ->where(fn ($q) => $q
                ->where('applies_to', 'semua')
                ->orWhere(fn ($q2) => $q2
                    ->where('applies_to', 'kelas_tertentu')
                    ->whereHas('schoolClasses', fn ($q3) => $q3->where('school_classes.id', $classId))
                )
            )
            ->pluck('date')
            ->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => true])
            ->all();
    }

    /** Date => true map of sekolah_khusus days in range for this class. */
    public static function getSpecialSchoolDates(Carbon $start, Carbon $end, ?int $classId): array
    {
        return static::whereBetween('date', [$start, $end])
            ->where('type', 'sekolah_khusus')
            ->where(fn ($q) => $q
                ->where('applies_to', 'semua')
                ->orWhere(fn ($q2) => $q2
                    ->where('applies_to', 'kelas_tertentu')
                    ->whereHas('schoolClasses', fn ($q3) => $q3->where('school_classes.id', $classId))
                )
            )
            ->pluck('date')
            ->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => true])
            ->all();
    }

    /**
     * Is this day a school day based on pre-fetched maps?
     * Use in bulk date-range loops to avoid per-day DB queries.
     */
    public static function isSchoolDay(Carbon $date, array $holidays, array $specialDays): bool
    {
        $ds = $date->format('Y-m-d');
        if ($date->isWeekend()) {
            return isset($specialDays[$ds]);
        }
        return ! isset($holidays[$ds]);
    }
}
