<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\ConductLog;
use Illuminate\Console\Command;

class SyncLateAttendanceFromSipinterCommand extends Command
{
    protected $signature = 'attendance:sync-sipinter-late {--date= : Filter tanggal tertentu (YYYY-MM-DD)}';

    protected $description = 'Sinkronisasi status terlambat pada tabel attendances agar hanya tercatat dari laporan SIPINTER';

    public function handle(): int
    {
        $dateFilter = $this->option('date');

        // 1. Ubah seluruh record attendance yang berstatus terlambat tetapi tidak punya laporan SIPINTER menjadi hadir
        $lateAttendancesQuery = Attendance::where('status', 'terlambat');
        if ($dateFilter) {
            $lateAttendancesQuery->whereDate('date', $dateFilter);
        }

        $revertedCount = 0;
        foreach ($lateAttendancesQuery->cursor() as $att) {
            $dateStr = $att->date->toDateString();
            if (! ConductLog::hasLateReportForDate((int) $att->user_id, $dateStr)) {
                $att->update(['status' => 'hadir']);
                $revertedCount++;
            }
        }

        // 2. Pastikan seluruh laporan keterlambatan di SIPINTER tercatat sebagai terlambat di tabel attendances
        $logsQuery = ConductLog::with('category')
            ->where('type', 'pelanggaran')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'rejected');
            });

        if ($dateFilter) {
            $logsQuery->whereDate('created_at', $dateFilter);
        }

        $syncedPairs = [];
        foreach ($logsQuery->cursor() as $log) {
            if (! $log->student_id || ! $log->isLateSchoolArrival()) {
                continue;
            }
            $dateStr = ($log->created_at ?? now())->toDateString();
            $key = $log->student_id . '|' . $dateStr;
            if (isset($syncedPairs[$key])) {
                continue;
            }
            $syncedPairs[$key] = true;
            ConductLog::syncAttendanceForStudentDate(
                (int) $log->student_id,
                $dateStr,
                $log->created_at?->format('H:i:s')
            );
        }

        $this->info("Selesai: {$revertedCount} presensi lewat 07:15 (tanpa SIPINTER) diubah ke Hadir; " . count($syncedPairs) . " laporan keterlambatan SIPINTER disinkronkan.");

        return self::SUCCESS;
    }
}

