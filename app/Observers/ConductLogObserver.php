<?php

namespace App\Observers;

use App\Models\ConductLog;

class ConductLogObserver
{
    public function created(ConductLog $log): void
    {
        if ($log->student_id) {
            ConductLog::syncAttendanceForStudentDate(
                (int) $log->student_id,
                $log->created_at ?? now(),
                $log->created_at?->format('H:i:s')
            );
        }
    }

    public function updated(ConductLog $log): void
    {
        if ($log->student_id) {
            ConductLog::syncAttendanceForStudentDate(
                (int) $log->student_id,
                $log->created_at ?? now(),
                $log->created_at?->format('H:i:s')
            );
        }
    }

    public function deleted(ConductLog $log): void
    {
        if ($log->student_id) {
            ConductLog::syncAttendanceForStudentDate(
                (int) $log->student_id,
                $log->created_at ?? now()
            );
        }
    }
}
