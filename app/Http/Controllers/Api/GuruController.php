<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EarlyCheckoutRequest;
use App\Models\ForgotAttendanceRequest;
use App\Models\Holiday;
use App\Models\Permit;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuruController extends Controller
{
    public function layanan(): JsonResponse
    {
        $waliKelas = SchoolClass::with('homeroomTeacher')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'class_name' => $c->name,
                'grade' => $c->grade,
                'homeroom_teacher' => $c->homeroomTeacher?->name ?? 'Belum Ditentukan',
                'nip' => $c->homeroomTeacher?->nip ?? '—',
                'phone' => $c->homeroomTeacher?->phone,
                'photo_url' => $c->homeroomTeacher?->photo_url,
            ]);

        $extracurriculars = \App\Models\Extracurricular::with(['teachers', 'pembina'])
            ->orderBy('name')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'pembina_names' => $e->pembina_names,
                'contact_person' => $e->contact_person,
            ]);

        $gurus = User::where('role', 'guru')
            ->orderBy('name')
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'nip' => $g->nip ?? '—',
                'subject' => $g->subject ?? 'Guru Pengajar',
                'phone' => $g->phone,
                'photo_url' => $g->photo_url,
            ]);

        $dayNames = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $piketSchedule = \App\Models\Schedule::with(['teacher', 'subject', 'schoolClass'])
            ->whereIn('day', [1, 2, 3, 4, 5, 6])
            ->orderBy('day')
            ->orderBy('period')
            ->get()
            ->groupBy('day')
            ->map(fn($items, $day) => [
                'day' => (int) $day,
                'day_name' => $dayNames[$day] ?? '—',
                'sessions' => $items->map(fn($s) => [
                    'teacher_name' => $s->teacher?->name ?? '—',
                    'subject' => $s->subject?->name ?? 'Mapel',
                    'class_name' => $s->schoolClass?->name ?? 'Kelas',
                    'period' => $s->period,
                ])->values()
            ])->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'wali_kelas' => $waliKelas,
                'extracurriculars' => $extracurriculars,
                'gurus' => $gurus,
                'piket_schedule' => $piketSchedule,
            ]
        ]);
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard(): JsonResponse
    {
        /** @var \App\Models\User $guru */
        $guru = Auth::user();
        $guru->load('homeroomClass');
        $classId = $guru->homeroomClass?->id;

        $totalStudents = $classId
            ? User::where('role', 'siswa')->where('class_id', $classId)->count()
            : 0;

        $isPrivileged = $guru->isBk() || $guru->role === 'admin';

        $pendingPermits = Permit::where('status', 'pending')
            ->when(! $isPrivileged && $classId, fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', $classId)))
            ->when(! $isPrivileged && ! $classId, fn($q) => $q->whereRaw('0=1'))
            ->count();

        $pendingEarlyCheckouts = EarlyCheckoutRequest::where('status', 'pending')->count();

        $pendingForgotAttendances = ForgotAttendanceRequest::where('status', 'pending')
            ->when(! $isPrivileged && $classId, fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', $classId)))
            ->when(! $isPrivileged && ! $classId, fn($q) => $q->whereRaw('0=1'))
            ->count();

        $alerts = User::where('role', 'siswa')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->whereHas('conductLogs', fn($q) => $q->where('type', 'pelanggaran')->orWhereHas('category', fn($c) => $c->where('type', 'pelanggaran')))
            ->withCount(['conductLogs as pelanggaran_count' => fn($q) => $q->where('type', 'pelanggaran')->orWhereHas('category', fn($c) => $c->where('type', 'pelanggaran'))])
            ->orderByDesc('pelanggaran_count')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'name'              => $s->name,
                'class'             => $s->schoolClass?->name ?? '—',
                'pelanggaran_count' => $s->pelanggaran_count,
            ]);

        $weeklyJournals = \App\Models\TeacherJournal::where('teacher_id', $guru->id)
            ->with(['schoolClass', 'subject', 'tp', 'absences.student'])
            ->orderByDesc('date')
            ->orderByDesc('period')
            ->limit(50)
            ->get()
            ->groupBy(function ($j) {
                $date = Carbon::parse($j->date);
                $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY)->translatedFormat('d M Y');
                $endOfWeek   = $date->copy()->endOfWeek(Carbon::SUNDAY)->translatedFormat('d M Y');
                return "$startOfWeek - $endOfWeek";
            })
            ->map(function ($journals, $weekRange) {
                return [
                    'week_range' => $weekRange,
                    'count'      => $journals->count(),
                    'journals'   => $journals->map(fn($j) => [
                        'id'              => $j->id,
                        'date_formatted'  => Carbon::parse($j->date)->translatedFormat('l, d M Y'),
                        'class_name'      => $j->schoolClass?->name ?? '—',
                        'subject_name'    => $j->subject?->name ?? '—',
                        'period'          => $j->period_display,
                        'material'        => $j->material,
                        'activity'        => $j->activity,
                        'notes'           => $j->notes,
                        'tp_code'         => $j->tp?->code,
                        'tp_description'  => $j->learning_objectives ?? $j->tp?->description,
                        'absent_students' => $j->absences->map(fn($a) => [
                            'student_name' => $a->student?->name ?? '—',
                            'status'       => $a->status,
                        ])->values(),
                    ])->values(),
                ];
            })
            ->values();

        $myExtracurriculars = \App\Models\Extracurricular::where('pembina_id', $guru->id)
            ->orWhereHas('teachers', fn ($q) => $q->where('users.id', $guru->id))
            ->withCount(['activeMembers as members_count'])
            ->get()
            ->map(fn ($e) => [
                'id'             => $e->id,
                'name'           => $e->name,
                'members_count'  => $e->members_count,
                'contact_person' => $e->contact_person,
                'logo_url'       => $e->logo ? asset('storage/' . $e->logo) : null,
            ]);

        $subjects = $guru->subjects()->pluck('name')->toArray();
        if (empty($subjects) && $guru->subject) {
            $subjects = array_filter(array_map('trim', explode(',', $guru->subject)));
        }

        // Jadwal Mengajar Hari Ini & Perminggu
        $todayIso = (int) now()->dayOfWeekIso; // 1=Senin .. 7=Minggu
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

        $mergeApiSchedules = function ($schedules) use ($dayNames) {
            $merged = [];
            foreach ($schedules as $sch) {
                $classId   = $sch->class_id;
                $subjectId = $sch->subject_id;
                $period    = (int) $sch->period;
                $startTime = Carbon::parse($sch->start_time)->format('H:i');
                $endTime   = Carbon::parse($sch->end_time)->format('H:i');

                $lastIdx = count($merged) - 1;
                if ($lastIdx >= 0) {
                    $prev = &$merged[$lastIdx];
                    if ($prev['class_id'] == $classId 
                        && $prev['subject_id'] == $subjectId 
                        && $period == $prev['period_end'] + 1
                    ) {
                        $prev['period_end']     = $period;
                        $prev['end_time']       = $endTime;
                        $prev['periods'][]      = $period;
                        $prev['period_display'] = "Jam ke-{$prev['period_start']} - {$period}";
                        continue;
                    }
                }

                $merged[] = [
                    'id'             => $sch->id,
                    'day'            => $sch->day,
                    'day_name'       => $dayNames[$sch->day] ?? '—',
                    'period'         => $period,
                    'period_start'   => $period,
                    'period_end'     => $period,
                    'period_display' => "Jam ke-{$period}",
                    'periods'        => [$period],
                    'start_time'     => $startTime,
                    'end_time'       => $endTime,
                    'room'           => $sch->room,
                    'class_id'       => $classId,
                    'class_name'     => $sch->schoolClass?->name ?? '—',
                    'subject_id'     => $subjectId,
                    'subject_name'   => $sch->subject?->name ?? '—',
                ];
            }
            return array_values($merged);
        };

        $rawTodaySchedules = \App\Models\Schedule::where('teacher_id', $guru->id)
            ->where('day', $todayIso)
            ->with(['schoolClass', 'subject'])
            ->orderBy('period')
            ->orderBy('start_time')
            ->get();

        $todaySchedules = $mergeApiSchedules($rawTodaySchedules);

        $allWeekly = \App\Models\Schedule::where('teacher_id', $guru->id)
            ->with(['schoolClass', 'subject'])
            ->orderBy('day')
            ->orderBy('period')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');

        $weeklySchedules = collect([1, 2, 3, 4, 5, 6])->map(function ($dNum) use ($allWeekly, $dayNames, $mergeApiSchedules) {
            $items = $allWeekly->get($dNum, collect());
            $mergedItems = $mergeApiSchedules($items);
            return [
                'day'       => $dNum,
                'day_name'  => $dayNames[$dNum] ?? '—',
                'count'     => count($mergedItems),
                'schedules' => $mergedItems,
            ];
        })->values();

        return response()->json([
            'is_homeroom'                 => (bool) $classId,
            'homeroom_class_id'           => $classId,
            'homeroom_class_name'         => $guru->homeroomClass?->name,
            'my_subjects'                 => array_values($subjects),
            'total_students'              => $totalStudents,
            'pending_permits'             => $pendingPermits,
            'pending_early_checkouts'     => $pendingEarlyCheckouts,
            'pending_forgot_attendances'  => $pendingForgotAttendances,
            'recent_alerts'               => $alerts,
            'weekly_journals'             => $weeklyJournals,
            'my_extracurriculars'         => $myExtracurriculars,
            'today_day_name'              => $dayNames[$todayIso] ?? 'Hari Ini',
            'today_schedules'             => $todaySchedules,
            'weekly_schedules'            => $weeklySchedules,
        ]);
    }

    // ─── Kelas ────────────────────────────────────────────────────────────────

    public function classes(): JsonResponse
    {
        $classes = SchoolClass::orderBy('name')
            ->withCount(['students' => fn($q) => $q->where('role', 'siswa')])
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'grade'         => $c->grade,
                'student_count' => $c->students_count,
            ]);

        return response()->json(['classes' => $classes]);
    }

    // ─── Absensi Harian ───────────────────────────────────────────────────────

    public function attendanceDaily(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date'     => 'required|date',
        ]);

        $classId = (int) $request->input('class_id');
        $date    = $request->input('date');

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->with(['attendances' => fn($q) => $q->whereDate('date', $date)])
            ->orderBy('name')
            ->get();

        $studentIds = $students->pluck('id')->all();
        $approvedEarlyCheckouts = EarlyCheckoutRequest::whereIn('student_id', $studentIds)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->pluck('student_id')
            ->mapWithKeys(fn($id) => [$id => true])
            ->all();

        $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'dispensasi' => 0];
        $rows = [];

        foreach ($students as $student) {
            $att              = $student->attendances->first();
            $hasEarlyApproval = isset($approvedEarlyCheckouts[$student->id]);
            $status           = $att ? $att->effectiveStatus($hasEarlyApproval) : 'alpa';
            if (isset($summary[$status])) $summary[$status]++;
            $rows[] = [
                'id'            => $student->id,
                'name'          => $student->name,
                'nis'           => $student->nis,
                'status'        => $status,
                'check_in_time' => $att?->check_in_time,
                'has_early_checkout' => $hasEarlyApproval,
            ];
        }

        return response()->json([
            'date'    => $date,
            'class_id' => $classId,
            'summary' => $summary,
            'students' => $rows,
        ]);
    }

    // ─── Rekap Absensi ────────────────────────────────────────────────────────

    public function attendanceRekap(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'month'    => 'required|integer|min:1|max:12',
            'year'     => 'required|integer|min:2020',
        ]);

        $classId = (int) $request->input('class_id');
        $month   = (int) $request->input('month');
        $year    = (int) $request->input('year');
        $today   = today();

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $holidays    = Holiday::getHolidayDates($start, $end, $classId);
        $specialDays = Holiday::getSpecialSchoolDates($start, $end, $classId);

        $allDays    = [];
        $schoolDays = [];
        $offDays    = [];
        $cursor     = $start->copy();
        while ($cursor->lte($end)) {
            $dateStr = $cursor->format('Y-m-d');
            $allDays[] = $dateStr;
            if (Holiday::isSchoolDay($cursor, $holidays, $specialDays)) {
                $schoolDays[] = $dateStr;
            } else {
                $offDays[$dateStr] = true;
            }
            $cursor->addDay();
        }

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get();

        $attendances = Attendance::whereHas('student', fn($q) => $q->where('class_id', $classId))
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        $approvedEarlyCheckouts = EarlyCheckoutRequest::whereIn('student_id', $students->pluck('id'))
            ->whereBetween('date', [$start, $end])
            ->where('status', 'approved')
            ->get(['student_id', 'date'])
            ->groupBy('student_id')
            ->map(fn($g) => $g->mapWithKeys(fn($r) => [$r->date->format('Y-m-d') => true])->all());

        $studentData = $students->map(function ($student) use ($attendances, $schoolDays, $approvedEarlyCheckouts, $offDays, $today) {
            $recs             = $attendances->get($student->id, collect())->keyBy(fn($a) => $a->date->format('Y-m-d'));
            $studentApprovals = $approvedEarlyCheckouts->get($student->id, []);
            $counts           = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'dispensasi' => 0];
            $statuses         = [];

            foreach ($schoolDays as $dateStr) {
                $day = Carbon::parse($dateStr);
                if ($day->gt($today)) {
                    $statuses[$dateStr] = 'future';
                    continue;
                }
                $att              = $recs->get($dateStr);
                $hasEarlyApproval = isset($studentApprovals[$dateStr]);
                $status           = $att ? $att->effectiveStatus($hasEarlyApproval) : 'alpa';
                $statuses[$dateStr] = $status;
                if (isset($counts[$status])) $counts[$status]++;
            }

            return [
                'id'       => $student->id,
                'name'     => $student->name,
                'nis'      => $student->nis,
                'statuses' => $statuses,
                'counts'   => $counts,
            ];
        });

        return response()->json([
            'class_id'    => $classId,
            'month'       => $month,
            'year'        => $year,
            'all_days'    => $allDays,
            'school_days' => $schoolDays,
            'off_days'    => array_keys($offDays),
            'students'    => $studentData->values(),
        ]);
    }

    // ─── Izin / Sakit / Dispensasi ────────────────────────────────────────────

    public function permits(Request $request): JsonResponse
    {
        /** @var \App\Models\User $guru */
        $guru    = Auth::user();
        $guru->load('homeroomClass');
        $status  = $request->input('status', 'pending');
        $classId = $request->input('class_id');
        $page    = (int) $request->input('page', 1);

        $query = Permit::with('student.schoolClass')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when(
                $classId !== null && $classId !== 'all' && is_numeric($classId),
                fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', (int) $classId)),
                fn($q) => $q->when(
                    $classId === null && $guru->homeroomClass,
                    fn($q2) => $q2->whereHas('student', fn($s) => $s->where('class_id', $guru->homeroomClass->id))
                )
            )
            ->latest();

        $paginated = $query->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->map(fn($p) => $this->permitPayload($p)),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function approvePermit(Permit $permit): JsonResponse
    {
        $this->authorizePermitAction($permit);

        $permit->update(['status' => 'approved', 'approved_by' => Auth::id()]);
        $this->syncPermitAttendance($permit);

        NotificationService::send(
            $permit->student_id,
            "{$permit->typeLabel()} Disetujui",
            "Pengajuan {$permit->typeLabel()} kamu untuk tanggal {$permit->start_date->isoFormat('D MMM Y')} telah disetujui.",
            'success',
        );

        return response()->json(['message' => "{$permit->typeLabel()} disetujui."]);
    }

    public function rejectPermit(Request $request, Permit $permit): JsonResponse
    {
        $this->authorizePermitAction($permit);
        $request->validate(['rejection_note' => 'required|string|max:255']);

        $permit->update([
            'status'         => 'rejected',
            'approved_by'    => Auth::id(),
            'rejection_note' => $request->rejection_note,
        ]);

        NotificationService::send(
            $permit->student_id,
            "{$permit->typeLabel()} Ditolak",
            "Pengajuan {$permit->typeLabel()} kamu ditolak. Alasan: {$request->rejection_note}",
            'warning',
        );

        return response()->json(['message' => "{$permit->typeLabel()} ditolak."]);
    }

    // ─── Lupa Absen ───────────────────────────────────────────────────────────

    public function forgotAttendance(Request $request): JsonResponse
    {
        /** @var \App\Models\User $guru */
        $guru    = Auth::user();
        $guru->load('homeroomClass');
        $status  = $request->input('status', 'pending');
        $classId = $request->input('class_id');
        $page    = (int) $request->input('page', 1);

        $query = ForgotAttendanceRequest::with('student.schoolClass')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when(
                $classId !== null && $classId !== 'all' && is_numeric($classId),
                fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', (int) $classId)),
                fn($q) => $q->when(
                    $classId === null && $guru->homeroomClass,
                    fn($q2) => $q2->whereHas('student', fn($s) => $s->where('class_id', $guru->homeroomClass->id))
                )
            )
            ->latest();

        $paginated = $query->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->map(fn($r) => [
                'id'           => $r->id,
                'student_name' => $r->student?->name ?? '—',
                'class_name'   => $r->student?->schoolClass?->name ?? '—',
                'date'         => $r->date?->toDateString(),
                'reason'       => $r->reason,
                'status'       => $r->status,
                'teacher_note' => $r->teacher_note,
                'reviewed_at'  => $r->reviewed_at?->toDateTimeString(),
                'student_stats'=> $r->student?->getAttendanceStatsSummary(),
            ]),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function approveForgotAttendance(ForgotAttendanceRequest $forgotAttendance): JsonResponse
    {
        $this->authorizeForgotAttendance($forgotAttendance);

        Attendance::updateOrCreate(
            ['user_id' => $forgotAttendance->student_id, 'date' => $forgotAttendance->date->toDateString()],
            ['status' => 'hadir']
        );

        $forgotAttendance->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        NotificationService::send(
            $forgotAttendance->student_id,
            'Lupa Absen Disetujui',
            'Pengajuan lupa absen tanggal ' . $forgotAttendance->date->isoFormat('D MMMM Y') . ' telah disetujui.',
            'success',
        );

        return response()->json(['message' => 'Disetujui. Presensi dicatat sebagai Hadir.']);
    }

    public function rejectForgotAttendance(Request $request, ForgotAttendanceRequest $forgotAttendance): JsonResponse
    {
        $this->authorizeForgotAttendance($forgotAttendance);
        $request->validate(['teacher_note' => 'required|string|max:255']);

        $forgotAttendance->update([
            'status'       => 'rejected',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'teacher_note' => $request->teacher_note,
        ]);

        NotificationService::send(
            $forgotAttendance->student_id,
            'Lupa Absen Ditolak',
            'Pengajuan lupa absen tanggal ' . $forgotAttendance->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $request->teacher_note,
            'warning',
        );

        return response()->json(['message' => 'Pengajuan ditolak.']);
    }

    // ─── Pulang Lebih Awal ────────────────────────────────────────────────────

    public function earlyCheckouts(Request $request): JsonResponse
    {
        /** @var \App\Models\User $guru */
        $guru    = Auth::user();
        $guru->load('homeroomClass');
        $status  = $request->input('status', 'pending');
        $classId = $request->input('class_id');
        $page    = (int) $request->input('page', 1);

        $query = EarlyCheckoutRequest::with('student.schoolClass')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when(
                $classId !== null && $classId !== 'all' && is_numeric($classId),
                fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', (int) $classId)),
                fn($q) => $q->when(
                    $classId === null && $guru->homeroomClass,
                    fn($q2) => $q2->whereHas('student', fn($s) => $s->where('class_id', $guru->homeroomClass->id))
                )
            )
            ->orderByDesc('date')
            ->orderBy('requested_time');

        $paginated = $query->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->map(fn($r) => [
                'id'             => $r->id,
                'student_name'   => $r->student?->name ?? '—',
                'class_name'     => $r->student?->schoolClass?->name ?? '—',
                'date'           => $r->date?->toDateString(),
                'requested_time' => $r->requested_time,
                'reason'         => $r->reason,
                'status'         => $r->status,
                'reviewer_note'  => $r->reviewer_note,
                'reviewed_at'    => $r->reviewed_at?->toDateTimeString(),
            ]),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function approveEarlyCheckout(Request $request, EarlyCheckoutRequest $earlyCheckout): JsonResponse
    {
        if (! $earlyCheckout->isPending()) {
            return response()->json(['message' => 'Pengajuan ini sudah diproses.'], 403);
        }

        $data = $request->validate(['reviewer_note' => 'nullable|string|max:255']);

        $earlyCheckout->update([
            'status'        => 'approved',
            'reviewed_by'   => Auth::id(),
            'reviewed_at'   => now(),
            'reviewer_note' => $data['reviewer_note'] ?? null,
        ]);

        NotificationService::send(
            $earlyCheckout->student_id,
            'Izin Pulang Awal Disetujui',
            'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . ' telah disetujui.',
            'success',
        );

        return response()->json(['message' => 'Pengajuan disetujui.']);
    }

    public function rejectEarlyCheckout(Request $request, EarlyCheckoutRequest $earlyCheckout): JsonResponse
    {
        if (! $earlyCheckout->isPending()) {
            return response()->json(['message' => 'Pengajuan ini sudah diproses.'], 403);
        }

        $data = $request->validate(['reviewer_note' => 'required|string|max:255']);

        $earlyCheckout->update([
            'status'        => 'rejected',
            'reviewed_by'   => Auth::id(),
            'reviewed_at'   => now(),
            'reviewer_note' => $data['reviewer_note'],
        ]);

        NotificationService::send(
            $earlyCheckout->student_id,
            'Izin Pulang Awal Ditolak',
            'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $data['reviewer_note'],
            'warning',
        );

        return response()->json(['message' => 'Pengajuan ditolak.']);
    }

    // ─── Conduct / Pelanggaran ────────────────────────────────────────────────

    public function conduct(Request $request): JsonResponse
    {
        $request->validate(['class_id' => 'required|exists:classes,id']);

        $classId = (int) $request->input('class_id');

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->withCount([
                'conductLogs as prestasi_count'    => fn($q) => $q->whereIn('type', ['prestasi', 'positif'])->orWhereHas('category', fn($c) => $c->whereIn('type', ['prestasi', 'positif'])),
                'conductLogs as pelanggaran_count' => fn($q) => $q->where('type', 'pelanggaran')->orWhereHas('category', fn($c) => $c->where('type', 'pelanggaran')),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'               => $s->id,
                'name'             => $s->name,
                'nis'              => $s->nis,
                'prestasi_count'   => $s->prestasi_count,
                'pelanggaran_count' => $s->pelanggaran_count,
            ]);

        return response()->json(['students' => $students]);
    }

    // ─── Extracurricular Member Approvals (Pembina) ───────────────────────────

    public function pendingExtracurricularMembers(): JsonResponse
    {
        $guru = Auth::user();

        $extraIds = Extracurricular::where('pembina_id', $guru->id)
            ->orWhereHas('teachers', fn($q) => $q->where('users.id', $guru->id))
            ->pluck('id');

        $pendingMembers = ExtracurricularMember::whereIn('extracurricular_id', $extraIds)
            ->whereIn('status', ['pending_join', 'pending_leave'])
            ->with(['student:id,name,class_id,nis', 'student.schoolClass:id,name', 'extracurricular:id,name'])
            ->latest()
            ->get()
            ->map(fn($m) => [
                'id'                   => $m->id,
                'extracurricular_id'   => $m->extracurricular_id,
                'extracurricular_name' => $m->extracurricular?->name ?? '—',
                'student_id'           => $m->user_id,
                'student_name'         => $m->student?->name ?? '—',
                'student_nis'          => $m->student?->nis,
                'class_name'           => $m->student?->schoolClass?->name ?? '—',
                'status'               => $m->status,
                'status_label'         => $m->status === 'pending_join' ? 'Pengajuan Masuk' : 'Pengajuan Keluar',
                'requested_at'         => $m->created_at->toIso8601String(),
            ]);

        return response()->json(['pending_members' => $pendingMembers]);
    }

    public function approveExtracurricularMember(int $id): JsonResponse
    {
        $guru   = Auth::user();
        $member = ExtracurricularMember::with(['extracurricular', 'student'])->findOrFail($id);

        if (!in_array($member->status, ['pending_join', 'pending_leave'])) {
            return response()->json(['message' => 'Pengajuan ini sudah diproses.'], 422);
        }

        if ($member->status === 'pending_join') {
            $member->update([
                'status'      => 'active',
                'approved_by' => $guru->id,
                'approved_at' => now(),
            ]);
            $msg = 'Pendaftaran ekstrakurikuler ' . $member->extracurricular?->name . ' disetujui.';
            if ($member->student) {
                NotificationService::send(
                    $member->user_id,
                    'Pendaftaran Ekstra Disetujui! 🎉',
                    "Selamat! Pendaftaran Anda di ekstrakurikuler {$member->extracurricular?->name} telah disetujui oleh Pembina.",
                    'success'
                );
            }
        } else {
            $member->update([
                'status'      => 'inactive',
                'approved_by' => $guru->id,
                'approved_at' => now(),
            ]);
            $msg = 'Pengajuan keluar dari ekstrakurikuler ' . $member->extracurricular?->name . ' disetujui.';
            if ($member->student) {
                NotificationService::send(
                    $member->user_id,
                    'Pengajuan Keluar Ekstra Disetujui',
                    "Pengajuan keluar dari ekstrakurikuler {$member->extracurricular?->name} telah disetujui.",
                    'info'
                );
            }
        }

        return response()->json(['message' => $msg]);
    }

    public function rejectExtracurricularMember(int $id): JsonResponse
    {
        $guru   = Auth::user();
        $member = ExtracurricularMember::with(['extracurricular', 'student'])->findOrFail($id);

        if (!in_array($member->status, ['pending_join', 'pending_leave'])) {
            return response()->json(['message' => 'Pengajuan ini sudah diproses.'], 422);
        }

        if ($member->status === 'pending_join') {
            $member->update([
                'status'      => 'rejected',
                'approved_by' => $guru->id,
                'approved_at' => now(),
            ]);
            $msg = 'Pendaftaran ekstrakurikuler ditolak.';
            if ($member->student) {
                NotificationService::send(
                    $member->user_id,
                    'Pendaftaran Ekstra Ditolak',
                    "Mohon maaf, pendaftaran Anda di ekstrakurikuler {$member->extracurricular?->name} belum dapat disetujui.",
                    'danger'
                );
            }
        } else {
            $member->update([
                'status'      => 'active',
                'approved_by' => $guru->id,
                'approved_at' => now(),
            ]);
            $msg = 'Pengajuan keluar dari ekstrakurikuler ditolak.';
        }

        return response()->json(['message' => $msg]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function permitPayload(Permit $p): array
    {
        return [
            'id'             => $p->id,
            'student_name'   => $p->student?->name ?? '—',
            'class_name'     => $p->student?->schoolClass?->name ?? '—',
            'type'           => $p->type,
            'type_label'     => $p->typeLabel(),
            'start_date'     => $p->start_date?->toDateString(),
            'end_date'       => $p->end_date?->toDateString(),
            'reason'         => $p->reason,
            'status'         => $p->status,
            'rejection_note' => $p->rejection_note,
            'file_url'       => $p->file ? Storage::disk('public')->url($p->file) : null,
            'student_stats'  => $p->student?->getAttendanceStatsSummary(),
        ];
    }

    private function authorizePermitAction(Permit $permit): void
    {
        if (! $permit->isPending()) abort(403, 'Pengajuan ini sudah diproses.');
    }

    private function authorizeForgotAttendance(ForgotAttendanceRequest $req): void
    {
        if (! $req->isPending()) abort(403, 'Pengajuan ini sudah diproses.');
    }

    private function syncPermitAttendance(Permit $permit): void
    {
        $current = $permit->start_date->copy();
        while ($current->lte($permit->end_date)) {
            if (! $current->isSunday()) {
                Attendance::updateOrCreate(
                    ['user_id' => $permit->student_id, 'date' => $current->toDateString()],
                    ['status' => $permit->type, 'check_in_time' => null]
                );
            }
            $current->addDay();
        }
    }
}
