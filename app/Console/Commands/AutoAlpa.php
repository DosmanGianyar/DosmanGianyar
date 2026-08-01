<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AutoAlpa extends Command
{
    protected $signature   = 'attendance:auto-alpa {--date= : Target date (Y-m-d), defaults to today}';
    protected $description = 'Mark absent (alpa) for students who did not check in today';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : today();

        // For weekend: only mark alpa for classes that have a sekolah_khusus entry.
        // For weekdays: skip classes that have a libur entry.
        // Pre-fetch all holiday/special entries for this date once.
        $globalHoliday  = Holiday::holidayExistsFor($date, null);        // applies_to=semua libur
        $globalSpecial  = Holiday::specialSchoolDayExistsFor($date, null); // applies_to=semua sekolah_khusus

        // Cache per-class results to avoid N+1 queries
        $classOffCache = [];
        $classOffFor = function (int $classId) use ($date, $globalHoliday, $globalSpecial, &$classOffCache): bool {
            if (! isset($classOffCache[$classId])) {
                $classOffCache[$classId] = Holiday::isOffDayFor($date, $classId);
            }
            return $classOffCache[$classId];
        };

        $students  = User::where('role', 'siswa')->with(['schoolClass', 'parentAccounts'])->get();
        $alpaCount = 0;

        foreach ($students as $student) {
            // Skip if this day is an off-day for this student's class
            if ($classOffFor((int) $student->class_id)) continue;

            $exists = Attendance::where('user_id', $student->id)
                ->whereDate('date', $date)
                ->exists();

            if (! $exists) {
                Attendance::create([
                    'user_id'       => $student->id,
                    'date'          => $date->toDateString(),
                    'check_in_time' => null,
                    'status'        => 'alpa',
                ]);
                $alpaCount++;

                // Notifikasi Peringatan Alpa ke Orangtua
                \App\Services\NotificationService::notifyParentsOfStudent(
                    $student,
                    "⚠️ Peringatan Presensi — {$student->name}",
                    "PERHATIAN: {$student->name} belum melakukan presensi masuk sekolah hingga pukul 08:00 WITA. Status saat ini dicatat Alpa. Mohon konfirmasi ke pihak sekolah jika terdapat kendala.",
                    'warning',
                    route('orangtua.attendance.index')
                );
            }
        }

        $this->info("Done: {$alpaCount} student(s) marked alpa for {$date->toDateString()}.");
        return self::SUCCESS;
    }
}
