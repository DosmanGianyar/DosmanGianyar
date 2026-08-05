<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TeacherJournal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $guru */
        $guru = Auth::user();
        $guru->load('homeroomClass.students');
        $classId = $guru->homeroomClass?->id;

        $totalStudents = $classId
            ? User::where('role', 'siswa')->where('class_id', $classId)->count()
            : 0;

        // Siswa dengan pelanggaran terbanyak di kelas wali
        $recentAlerts = User::where('role', 'siswa')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->whereHas('conductLogs', fn($q) => $q->where('type', 'pelanggaran')->orWhereHas('category', fn($c) => $c->where('type', 'pelanggaran')))
            ->withCount(['conductLogs as pelanggaran_count' => fn($q) => $q->where('type', 'pelanggaran')->orWhereHas('category', fn($c) => $c->where('type', 'pelanggaran'))])
            ->orderByDesc('pelanggaran_count')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'name'  => $s->name,
                'class' => $s->schoolClass?->name ?? '—',
                'point' => $s->pelanggaran_count,
            ]);

        // Histori Jurnal Mengajar Guru per Minggu
        $weeklyJournals = TeacherJournal::where('teacher_id', $guru->id)
            ->with(['schoolClass', 'subject', 'tp', 'absences.student'])
            ->orderByDesc('date')
            ->orderByDesc('period')
            ->limit(50)
            ->get()
            ->groupBy(function ($j) {
                $date = Carbon::parse($j->date);
                $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY)->translatedFormat('d M Y');
                $endOfWeek   = $date->copy()->endOfWeek(Carbon::SUNDAY)->translatedFormat('d M Y');
                return "Minggu ($startOfWeek - $endOfWeek)";
            });

        $myExtracurriculars = \App\Models\Extracurricular::where('pembina_id', $guru->id)
            ->orWhereHas('teachers', fn ($q) => $q->where('users.id', $guru->id))
            ->withCount(['activeMembers as members_count'])
            ->get();

        // Jadwal Mengajar Hari Ini & Perminggu
        $todayIso = (int) now()->dayOfWeekIso; // 1=Senin .. 7=Minggu
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayDayName = $dayNames[$todayIso] ?? 'Hari Ini';

        $todaySchedules = \App\Models\Schedule::where('teacher_id', $guru->id)
            ->where('day', $todayIso)
            ->with(['schoolClass', 'subject'])
            ->orderBy('period')
            ->orderBy('start_time')
            ->get();

        $weeklySchedules = \App\Models\Schedule::where('teacher_id', $guru->id)
            ->with(['schoolClass', 'subject'])
            ->orderBy('day')
            ->orderBy('period')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');

        // Jurnal hari ini untuk mengecek jam mana yang sudah terisi
        $todayJournals = TeacherJournal::where('teacher_id', $guru->id)
            ->whereDate('date', today())
            ->get();

        $filledJournalKeys = [];
        foreach ($todayJournals as $tj) {
            $pStart = $tj->period ?: 1;
            $pEnd   = $tj->period_end ?: $pStart;
            for ($p = $pStart; $p <= $pEnd; $p++) {
                $filledJournalKeys["{$tj->class_id}_{$p}"] = true;
                if ($tj->subject_id) {
                    $filledJournalKeys["{$tj->class_id}_{$tj->subject_id}_{$p}"] = true;
                }
            }
        }

        // Merger jadwal berurutan untuk kelas & mapel yang sama
        $mergeSchedules = function ($schedules, $filledKeys = []) {
            $merged = [];
            foreach ($schedules as $sch) {
                $classId   = $sch->class_id;
                $subjectId = $sch->subject_id;
                $period    = (int) $sch->period;
                $startTime = \Carbon\Carbon::parse($sch->start_time)->format('H:i');
                $endTime   = \Carbon\Carbon::parse($sch->end_time)->format('H:i');

                $isFilled = isset($filledKeys["{$classId}_{$period}"]) || isset($filledKeys["{$classId}_{$subjectId}_{$period}"]);

                $lastIdx = count($merged) - 1;
                if ($lastIdx >= 0) {
                    $prev = &$merged[$lastIdx];
                    if ($prev['class_id'] == $classId 
                        && $prev['subject_id'] == $subjectId 
                        && $period == $prev['period_end'] + 1
                    ) {
                        $prev['period_end']   = $period;
                        $prev['end_time']     = $endTime;
                        $prev['periods'][]    = $period;
                        $prev['period_label'] = "Jam ke-{$prev['period_start']} - {$period}";
                        if ($isFilled) {
                            $prev['filled_periods'][] = $period;
                        }
                        $prev['is_filled'] = (count($prev['filled_periods']) === count($prev['periods']));
                        continue;
                    }
                }

                $merged[] = [
                    'id'             => $sch->id,
                    'day'            => $sch->day,
                    'class_id'       => $classId,
                    'class_name'     => $sch->schoolClass?->name ?? '—',
                    'subject_id'     => $subjectId,
                    'subject_name'   => $sch->subject?->name ?? '—',
                    'room'           => $sch->room,
                    'period'         => $period,
                    'period_start'   => $period,
                    'period_end'     => $period,
                    'period_label'   => "Jam ke-{$period}",
                    'periods'        => [$period],
                    'filled_periods' => $isFilled ? [$period] : [],
                    'is_filled'      => $isFilled,
                    'start_time'     => $startTime,
                    'end_time'       => $endTime,
                ];
            }
            return collect($merged);
        };

        $todaySchedulesMerged  = $mergeSchedules($todaySchedules, $filledJournalKeys);
        $weeklySchedulesMerged = $weeklySchedules->map(fn($group) => $mergeSchedules($group, $filledJournalKeys));

        $stats = [
            'alert_kritis'   => $recentAlerts->count(),
            'total_students' => $totalStudents,
            'total_journals' => TeacherJournal::where('teacher_id', $guru->id)->count(),
            'today_schedules_count' => $todaySchedules->count(),
        ];

        return view('guru.dashboard', compact(
            'guru',
            'stats',
            'recentAlerts',
            'weeklyJournals',
            'myExtracurriculars',
            'todaySchedules',
            'todaySchedulesMerged',
            'weeklySchedules',
            'weeklySchedulesMerged',
            'todayDayName',
            'dayNames',
            'filledJournalKeys'
        ));
    }
}
