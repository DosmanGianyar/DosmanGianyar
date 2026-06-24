<?php

namespace App\Observers;

use App\Models\BkLog;
use App\Models\ConductLog;

class ConductLogObserver
{
    private const BK_THRESHOLD = -75;

    public function created(ConductLog $log): void
    {
        $totalPoint = $log->student->conductLogs()->sum('point');

        if ($totalPoint <= self::BK_THRESHOLD) {
            $alreadyToday = BkLog::where('student_id', $log->student_id)
                ->whereDate('date', today())
                ->where('is_auto', true)
                ->exists();

            if (!$alreadyToday) {
                BkLog::create([
                    'student_id'    => $log->student_id,
                    'counselor_id'  => $log->teacher_id,
                    'coaching_note' => "Otomatis: total poin siswa mencapai {$totalPoint}. Perlu tindak lanjut BK.",
                    'point_at_time' => $totalPoint,
                    'is_auto'       => true,
                    'date'          => today(),
                ]);
            }
        }
    }
}
