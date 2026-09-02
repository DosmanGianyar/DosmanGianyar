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

    protected static function booted(): void
    {
        static::saved(function (Holiday $holiday) {
            if ($holiday->type === 'libur') {
                $query = Attendance::whereDate('date', $holiday->date)
                    ->where('status', 'alpa');

                if ($holiday->applies_to === 'kelas_tertentu') {
                    $classIds  = $holiday->schoolClasses()->pluck('classes.id');
                    $studentIds = User::whereIn('class_id', $classIds)->pluck('id');
                    $query->whereIn('user_id', $studentIds);
                }

                $query->delete();
            }
        });
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
        $setting = AttendanceSetting::forDay((int) $date->dayOfWeekIso);
        if ($setting && ! $setting->is_active) {
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
                    ->whereHas('schoolClasses', fn ($q3) => $q3->where('classes.id', $classId))
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
                    ->whereHas('schoolClasses', fn ($q3) => $q3->where('classes.id', $classId))
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
                    ->whereHas('schoolClasses', fn ($q3) => $q3->where('classes.id', $classId))
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
                    ->whereHas('schoolClasses', fn ($q3) => $q3->where('classes.id', $classId))
                )
            )
            ->pluck('date')
            ->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => true])
            ->all();
    }

    protected static ?array $cachedInactiveDays = null;

    /**
     * Get day_of_week (1=Monday ... 7=Sunday) list for days set as inactive in AttendanceSetting.
     */
    public static function getInactiveDaysOfWeek(): array
    {
        if (static::$cachedInactiveDays === null) {
            static::$cachedInactiveDays = AttendanceSetting::where('is_active', false)
                ->pluck('day_of_week')
                ->map(fn ($d) => (int) $d)
                ->all();
            if (empty(static::$cachedInactiveDays)) {
                static::$cachedInactiveDays = [7]; // Default Sunday as inactive if settings not initialized
            }
        }
        return static::$cachedInactiveDays;
    }

    /**
     * Is this day a school day based on pre-fetched maps and active attendance settings?
     * Use in bulk date-range loops to avoid per-day DB queries.
     */
    public static function isSchoolDay(Carbon $date, array $holidays, array $specialDays, ?array $inactiveDays = null): bool
    {
        $ds = $date->format('Y-m-d');
        $inactiveDays = $inactiveDays ?? static::getInactiveDaysOfWeek();
        $dayOfWeek = (int) $date->dayOfWeekIso;

        if (in_array($dayOfWeek, $inactiveDays, true)) {
            return isset($specialDays[$ds]);
        }

        return ! isset($holidays[$ds]);
    }
}
