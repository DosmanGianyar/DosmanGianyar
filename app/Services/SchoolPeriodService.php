<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\TeacherJournal;
use Illuminate\Support\Carbon;

class SchoolPeriodService
{
    /**
     * Structure of standard school periods.
     */
    public static function getPeriods(): array
    {
        return [
            ['period' => 1, 'name' => 'Jam ke-1', 'start' => '07:30', 'end' => '08:15', 'type' => 'lesson'],
            ['period' => 2, 'name' => 'Jam ke-2', 'start' => '08:15', 'end' => '09:00', 'type' => 'lesson'],
            ['period' => 3, 'name' => 'Jam ke-3', 'start' => '09:00', 'end' => '09:45', 'type' => 'lesson'],
            ['period' => null, 'name' => 'Istirahat Ke-1', 'start' => '09:45', 'end' => '10:00', 'type' => 'break'],
            ['period' => 4, 'name' => 'Jam ke-4', 'start' => '10:00', 'end' => '10:45', 'type' => 'lesson'],
            ['period' => 5, 'name' => 'Jam ke-5', 'start' => '10:45', 'end' => '11:30', 'type' => 'lesson'],
            ['period' => 6, 'name' => 'Jam ke-6', 'start' => '11:30', 'end' => '12:15', 'type' => 'lesson'],
            ['period' => null, 'name' => 'Istirahat Ke-2', 'start' => '12:15', 'end' => '12:30', 'type' => 'break'],
            ['period' => 7, 'name' => 'Jam ke-7', 'start' => '12:30', 'end' => '13:15', 'type' => 'lesson'],
            ['period' => 8, 'name' => 'Jam ke-8', 'start' => '13:15', 'end' => '14:00', 'type' => 'lesson'],
        ];
    }

    /**
     * Determine active period based on current time or given Carbon instance.
     */
    public static function getActivePeriodInfo(?Carbon $now = null): array
    {
        $now = $now ?? now();
        $timeStr = $now->format('H:i');
        $periods = static::getPeriods();

        if ($timeStr < '07:30') {
            return [
                'status'        => 'before_school',
                'label'         => 'Sebelum Jam Pelajaran (Persiapan Jam ke-1)',
                'active_period' => 1,
                'is_break'      => false,
                'time_range'    => '07:30 - 08:15',
            ];
        }

        if ($timeStr >= '14:00') {
            return [
                'status'        => 'after_school',
                'label'         => 'Pulang Sekolah (KBM Selesai)',
                'active_period' => null,
                'is_break'      => false,
                'time_range'    => '14:00 - Malam',
            ];
        }

        foreach ($periods as $p) {
            if ($timeStr >= $p['start'] && $timeStr < $p['end']) {
                return [
                    'status'        => $p['type'] === 'break' ? 'break' : 'active',
                    'label'         => $p['name'] . ' (' . $p['start'] . ' - ' . $p['end'] . ')',
                    'active_period' => $p['period'],
                    'is_break'      => $p['type'] === 'break',
                    'name'          => $p['name'],
                    'time_range'    => $p['start'] . ' - ' . $p['end'],
                ];
            }
        }

        return [
            'status'        => 'active',
            'label'         => 'Jam ke-8 (13:15 - 14:00)',
            'active_period' => 8,
            'is_break'      => false,
            'time_range'    => '13:15 - 14:00',
        ];
    }

    /**
     * Get live monitoring list of all classes for the active day and specified period.
     */
    public static function getLiveMonitoringData(?int $day = null, ?int $period = null, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $day = $day ?? (int) $now->dayOfWeekIso; // 1 = Senin, 6 = Sabtu
        $activeInfo = static::getActivePeriodInfo($now);
        
        $selectedPeriod = $period ?? ($activeInfo['active_period'] ?? 1);

        $classes = SchoolClass::orderBy('name')->get();
        
        // Fetch schedules for specified day & period
        $schedules = Schedule::with(['teacher', 'subject'])
            ->where('day', $day)
            ->where('period', $selectedPeriod)
            ->get()
            ->keyBy('class_id');

        // Fetch journals filled today for this period
        $todayJournalClassIds = TeacherJournal::whereDate('date', $now->toDateString())
            ->where(function ($q) use ($selectedPeriod) {
                $q->where('period', $selectedPeriod)
                  ->orWhere(function ($q2) use ($selectedPeriod) {
                      $q2->where('period', '<=', $selectedPeriod)
                         ->where('period_end', '>=', $selectedPeriod);
                  });
            })
            ->pluck('class_id')
            ->all();

        $monitoring = $classes->map(function ($class) use ($schedules, $todayJournalClassIds) {
            /** @var Schedule|null $sch */
            $sch = $schedules->get($class->id);

            if (! $sch) {
                return [
                    'class_id'           => $class->id,
                    'class_name'         => $class->name,
                    'has_schedule'       => false,
                    'teacher_id'         => null,
                    'teacher_name'       => null,
                    'subject_name'       => null,
                    'room'               => null,
                    'has_journal'        => false,
                    'status_message'     => 'Jadwal belum di-input',
                    'status_badge'       => 'warning',
                ];
            }

            $hasJournal = in_array($class->id, $todayJournalClassIds);

            return [
                'class_id'           => $class->id,
                'class_name'         => $class->name,
                'has_schedule'       => true,
                'teacher_id'         => $sch->teacher_id,
                'teacher_name'       => $sch->teacher?->name ?? '—',
                'teacher_nip'        => $sch->teacher?->nip ?? '—',
                'teacher_photo'      => $sch->teacher?->photo_url,
                'subject_name'       => $sch->subject?->name ?? '—',
                'room'               => $sch->room ?? '—',
                'has_journal'        => $hasJournal,
                'status_message'     => $hasJournal ? 'Sudah Mengisi Jurnal' : 'Belum Mengisi Jurnal',
                'status_badge'       => $hasJournal ? 'success' : 'danger',
            ];
        });

        return [
            'active_info'      => $activeInfo,
            'selected_period'  => $selectedPeriod,
            'selected_day'     => $day,
            'day_name'         => ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$day] ?? '—',
            'periods'          => static::getPeriods(),
            'monitoring_list'  => $monitoring->values()->all(),
            'total_classes'    => $monitoring->count(),
            'filled_schedules' => $monitoring->where('has_schedule', true)->count(),
            'missing_schedules'=> $monitoring->where('has_schedule', false)->count(),
            'journal_filled'   => $monitoring->where('has_journal', true)->count(),
        ];
    }
}
