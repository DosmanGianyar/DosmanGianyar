<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EarlyCheckoutRequest;
use App\Models\ForgotAttendanceRequest;
use App\Models\Holiday;
use App\Models\Permit;
use App\Models\User;
use Carbon\Carbon;

class StudentAttendanceDetailService
{
    public static function getDetail(int $studentId, int $month, int $year): array
    {
        $student = User::with('schoolClass.homeroomTeacher')->findOrFail($studentId);

        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();
        $today = now()->startOfDay();

        $classId     = $student->class_id;
        $holidays    = Holiday::getHolidayDates($start, $end, $classId);
        $specialDays = Holiday::getSpecialSchoolDates($start, $end, $classId);

        // Fetch attendances
        $attendances = Attendance::where('user_id', $studentId)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->date)->toDateString());

        // Fetch approved permits
        $permits = Permit::where('student_id', $studentId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get();

        // Fetch approved early checkouts
        $earlyCheckouts = EarlyCheckoutRequest::where('student_id', $studentId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'approved')
            ->get()
            ->keyBy(fn($ec) => $ec->date->toDateString());

        // Fetch forgot attendance requests
        $forgotRequests = ForgotAttendanceRequest::where('student_id', $studentId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn($fa) => $fa->date->toDateString());

        $dailyLogs = [];
        $counts = [
            'hadir'      => 0,
            'terlambat'  => 0,
            'izin'       => 0,
            'sakit'      => 0,
            'alpa'       => 0,
            'dispensasi' => 0,
            'lupa_absen' => 0,
            'libur'      => 0,
        ];

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr = $d->toDateString();

            // Skip future days if beyond today
            if ($d->gt($today)) continue;

            $att    = $attendances->get($dateStr);
            $permit = $permits->first(fn($p) => $dateStr >= $p->start_date->toDateString() && $dateStr <= $p->end_date->toDateString());
            $early  = $earlyCheckouts->get($dateStr);

            $isSchoolDay = Holiday::isSchoolDay($d, $holidays, $specialDays);
            if (! $isSchoolDay && ! $att && ! $permit) {
                $counts['libur']++;
                continue;
            }

            $status = 'alpa';
            $reason = null;

            if ($att) {
                $status = $att->effectiveStatus((bool)$early);
                if ($att->via_lupa_absen) {
                    $counts['lupa_absen']++;
                    $typeLabel = match($att->lupa_absen_type) { 'pulang' => 'Pulang', 'keduanya' => 'Datang & Pulang', default => 'Datang' };
                    $reason = 'Disetujui via Lupa Absen (' . $typeLabel . ')';
                }
            } elseif ($permit) {
                $status = $permit->type;
                $reason = $permit->reason ?? match($permit->type) { 'sakit' => 'Sakit', 'izin' => 'Izin', 'dispensasi' => 'Dispensasi', default => ucfirst($permit->type) };
            } else {
                $status = 'alpa';
                $reason = 'Tanpa Keterangan (Alpa)';
            }

            if (isset($counts[$status])) {
                $counts[$status]++;
            }

            $dailyLogs[] = [
                'date'           => $d->toDateString(),
                'date_formatted' => $d->isoFormat('dddd, D MMMM Y'),
                'status'         => $status,
                'via_lupa_absen' => (bool) ($att?->via_lupa_absen),
                'lupa_absen_type'=> $att?->lupa_absen_type,
                'check_in'       => $att?->check_in_time ? substr($att->check_in_time, 0, 5) : null,
                'check_out'      => $att?->check_out_time ? substr($att->check_out_time, 0, 5) : null,
                'reason'         => $reason,
                'photo_in_url'   => $att?->photo_url,
                'photo_out_url'  => $att?->check_out_photo_url,
            ];
        }

        return [
            'student' => [
                'id'         => $student->id,
                'name'       => $student->name,
                'nis'        => $student->nis ?? '—',
                'class_name' => $student->schoolClass?->name ?? '—',
            ],
            'month_name' => $start->isoFormat('MMMM Y'),
            'counts'     => $counts,
            'logs'       => array_reverse($dailyLogs),
        ];
    }
}
