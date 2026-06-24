<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoAlpa extends Command
{
    protected $signature   = 'attendance:auto-alpa {--date= : Target date (Y-m-d), defaults to today}';
    protected $description = 'Mark absent (alpa) for students who did not check in today';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : today();

        // Skip weekends
        if ($date->isWeekend()) {
            $this->info("Skipped: {$date->toDateString()} is a weekend.");
            return self::SUCCESS;
        }

        // Skip holidays
        if (Holiday::whereDate('date', $date)->exists()) {
            $this->info("Skipped: {$date->toDateString()} is a holiday.");
            return self::SUCCESS;
        }

        $students = User::where('role', 'siswa')->pluck('id');
        $alpaCount = 0;

        foreach ($students as $studentId) {
            $exists = Attendance::where('user_id', $studentId)
                ->whereDate('date', $date)
                ->exists();

            if (!$exists) {
                Attendance::create([
                    'user_id'       => $studentId,
                    'date'          => $date->toDateString(),
                    'check_in_time' => null,
                    'status'        => 'alpa',
                ]);
                $alpaCount++;
            }
        }

        $this->info("Done: {$alpaCount} student(s) marked alpa for {$date->toDateString()}.");
        return self::SUCCESS;
    }
}
