<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ConductLog;
use App\Models\EarlyCheckoutRequest;
use App\Models\Holiday;
use App\Models\StudentAchievement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Query bersama untuk data absensi/catatan/prestasi milik seorang siswa,
 * dipakai oleh siswa untuk data miliknya sendiri maupun orangtua untuk data anaknya.
 */
class StudentDataService
{
    public static function attendanceHistory(User $student, int $month, int $year): array
    {
        $month = max(1, min(12, $month));
        $year  = max(2020, min(now()->year + 1, $year));

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $rows = Attendance::where('user_id', $student->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->get(['date', 'check_in_time', 'check_out_time', 'status', 'is_fake_gps', 'photo', 'check_out_photo', 'updated_at']);

        $approvedDates = EarlyCheckoutRequest::where('student_id', $student->id)
            ->whereBetween('date', [$start, $end])
            ->where('status', 'approved')
            ->pluck('date')
            ->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => true])
            ->all();

        $records = $rows->map(fn ($r) => [
            'date'                => $r->date->format('Y-m-d'),
            'check_in_time'       => $r->check_in_time,
            'check_out_time'      => $r->check_out_time,
            'status'              => $r->effectiveStatus(isset($approvedDates[$r->date->format('Y-m-d')])),
            'is_fake_gps'         => (bool) $r->is_fake_gps,
            'check_in_photo_url'  => $r->photo_url,
            'check_out_photo_url' => $r->check_out_photo_url,
        ]);

        $recordedDates = $rows->pluck('date')->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => true])->all();
        $holidays      = Holiday::getHolidayDates($start, $end, $student->class_id);
        $specialDays   = Holiday::getSpecialSchoolDates($start, $end, $student->class_id);
        $today         = today();

        $synthetic = collect();
        for ($day = $start->copy(); $day->lt($today) && $day->lte($end); $day->addDay()) {
            $ds = $day->format('Y-m-d');
            if (! Holiday::isSchoolDay($day, $holidays, $specialDays)) continue;
            if (isset($recordedDates[$ds])) continue;
            $synthetic->push([
                'date'                => $ds,
                'check_in_time'       => null,
                'check_out_time'      => null,
                'status'              => 'alpa',
                'is_fake_gps'         => false,
                'check_in_photo_url'  => null,
                'check_out_photo_url' => null,
            ]);
        }

        $all = $synthetic->concat($records)->sortByDesc('date')->values();

        $summary = [
            'hadir'      => $all->where('status', 'hadir')->count(),
            'terlambat'  => $all->where('status', 'terlambat')->count(),
            'izin'       => $all->where('status', 'izin')->count(),
            'sakit'      => $all->where('status', 'sakit')->count(),
            'alpa'       => $all->where('status', 'alpa')->count(),
            'dispensasi' => $all->where('status', 'dispensasi')->count(),
        ];

        return [
            'month'       => $month,
            'year'        => $year,
            'summary'     => $summary,
            'records'     => $all,
            'server_time' => now()->toIso8601String(),
        ];
    }

    public static function conductLogs(User $student): array
    {
        $logs = ConductLog::with('category', 'teacher')
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        $prestasiCount    = $logs->filter(fn ($l) => ($l->category?->type ?? $l->type) === 'prestasi')->count();
        $pelanggaranCount = $logs->filter(fn ($l) => ($l->category?->type ?? $l->type) === 'pelanggaran')->count();

        return [
            'summary' => [
                'prestasi_count'    => $prestasiCount,
                'pelanggaran_count' => $pelanggaranCount,
            ],
            'logs' => $logs->map(fn ($log) => [
                'id'            => $log->id,
                'category_name' => $log->category?->name ?? ucfirst($log->type ?? 'Catatan'),
                'type'          => $log->category?->type ?? $log->type,
                'context'       => $log->category?->context,
                'note'          => $log->note,
                'photo_url'     => $log->photo ? Storage::disk('public')->url($log->photo) : null,
                'teacher_name'  => $log->teacher?->name,
                'date'          => $log->created_at->toDateString(),
                'created_at'    => $log->created_at->toIso8601String(),
            ])->values(),
        ];
    }

    public static function achievements(User $student): array
    {
        $achievements = StudentAchievement::with('category')
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $stats = [
            'pending'  => $achievements->where('status', 'pending')->count(),
            'approved' => $achievements->where('status', 'approved')->count(),
            'rejected' => $achievements->where('status', 'rejected')->count(),
        ];

        return [
            'stats'        => $stats,
            'achievements' => $achievements->map(fn ($a) => self::formatAchievement($a))->values(),
        ];
    }

    public static function formatAchievement(StudentAchievement $a): array
    {
        return [
            'id'               => $a->id,
            'title'            => $a->title,
            'category_name'    => $a->category?->name,
            'level'            => $a->level,
            'level_label'      => $a->levelLabel(),
            'rank'             => $a->rank,
            'achievement_date' => $a->achievement_date->toDateString(),
            'description'      => $a->description,
            'status'           => $a->status,
            'status_label'     => $a->statusLabel(),
            'rejection_reason' => $a->rejection_reason,
            'photo_url'        => $a->photoUrl(),
            'certificate_url'  => $a->certificateUrl(),
            'created_at'       => $a->created_at->toIso8601String(),
        ];
    }
}
