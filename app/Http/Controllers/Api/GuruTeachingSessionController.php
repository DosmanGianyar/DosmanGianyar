<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SessionAttendance;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuruTeachingSessionController extends Controller
{
    // GET /api/v1/guru/teaching-classes — semua kelas yang diajar guru
    public function classes(): JsonResponse
    {
        $teacher = Auth::user();

        $homeroomClass = $teacher->homeroomClass
            ? [['id' => $teacher->homeroomClass->id, 'name' => $teacher->homeroomClass->name, 'is_homeroom' => true]]
            : [];

        $teachingClasses = Schedule::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name'])
            ->get()
            ->map(fn ($s) => [
                'id'          => $s->schoolClass?->id,
                'name'        => $s->schoolClass?->name ?? '—',
                'subject_id'  => $s->subject_id,
                'subject_name'=> $s->subject?->name ?? '—',
                'day'         => $s->day,
                'day_name'    => $s->dayName(),
                'period'      => $s->period,
                'start_time'  => $s->start_time,
                'end_time'    => $s->end_time,
                'is_homeroom' => false,
            ])
            ->filter(fn ($s) => $s['id'] !== null)
            ->values();

        // Jika guru belum memiliki Jadwal Pelajaran spesifik di database,
        // tampilkan seluruh kelas aktif di sekolah sebagai opsi mengajar
        if ($teachingClasses->isEmpty()) {
            $teachingClasses = SchoolClass::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($c) => [
                    'id'          => $c->id,
                    'name'        => $c->name,
                    'subject_id'  => null,
                    'subject_name'=> '',
                    'day'         => null,
                    'day_name'    => '',
                    'period'      => null,
                    'start_time'  => null,
                    'end_time'    => null,
                    'is_homeroom' => false,
                ])
                ->values();
        }

        return response()->json([
            'homeroom_class'  => $teacher->homeroomClass ? ['id' => $teacher->homeroomClass->id, 'name' => $teacher->homeroomClass->name] : null,
            'teaching_classes'=> $teachingClasses,
        ]);
    }

    // GET /api/v1/guru/teaching-sessions?month=&year=&class_id=
    public function index(Request $request): JsonResponse
    {
        $teacher = Auth::user();

        $query = TeacherAttendance::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name', 'sessionAttendances']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }

        $allSessions = $query->orderByDesc('date')->orderBy('period')->get();

        // Kelompokkan sesi berdasarkan tanggal, class_id, subject_id, dan waktu pembuatan (sekali simpan)
        $grouped = [];
        foreach ($allSessions as $session) {
            $createdMinute = $session->created_at ? $session->created_at->format('Y-m-d H:i') : $session->date->format('Y-m-d');
            $key = "{$session->date->format('Y-m-d')}_{$session->class_id}_{$session->subject_id}_{$createdMinute}";

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'primary'     => $session,
                    'periods'     => [$session->period],
                    'session_ids' => [$session->id],
                    'attendances' => collect($session->sessionAttendances),
                ];
            } else {
                $grouped[$key]['periods'][] = $session->period;
                $grouped[$key]['session_ids'][] = $session->id;
                foreach ($session->sessionAttendances as $att) {
                    if (! $grouped[$key]['attendances']->contains('student_id', $att->student_id)) {
                        $grouped[$key]['attendances']->push($att);
                    }
                }
            }
        }

        // Format hasil gabungan sesi per submit
        $items = collect($grouped)->map(function ($group) {
            $primary = $group['primary'];
            sort($group['periods']);
            $minPeriod = min($group['periods']);
            $maxPeriod = max($group['periods']);

            $total = $group['attendances']->count();
            $hadir = $group['attendances']->where('status', 'hadir')->count();
            $alpha = $group['attendances']->where('status', 'tidak_hadir')->count();

            return [
                'id'             => $primary->id,
                'session_ids'    => $group['session_ids'],
                'class_id'       => $primary->class_id,
                'class_name'     => $primary->schoolClass?->name ?? '—',
                'subject_id'     => $primary->subject_id,
                'subject_name'   => $primary->subject?->name ?? '—',
                'date'           => $primary->date?->format('Y-m-d'),
                'period'         => $minPeriod,
                'period_end'     => $minPeriod !== $maxPeriod ? $maxPeriod : null,
                'period_display' => $minPeriod !== $maxPeriod ? "Jam ke-{$minPeriod} - {$maxPeriod}" : "Jam ke-{$minPeriod}",
                'start_time'     => $primary->start_time,
                'end_time'       => $primary->end_time,
                'status'         => $primary->status,
                'note'           => $primary->note,
                'total'          => $total,
                'hadir'          => $hadir,
                'alpha'          => $alpha,
            ];
        })->values();

        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->slice(($page - 1) * $perPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $page
        );

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    // GET /api/v1/guru/teaching-sessions/occupied-periods?class_id=&date=
    public function occupiedPeriods(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date'     => 'required|date',
        ]);

        $teacher = Auth::user();

        $sessions = TeacherAttendance::where('class_id', $request->class_id)
            ->whereDate('date', $request->date)
            ->with(['teacher:id,name', 'subject:id,name'])
            ->get();

        $occupied = $sessions->map(fn ($s) => [
            'period'       => (int) $s->period,
            'teacher_id'   => $s->teacher_id,
            'teacher_name' => $s->teacher?->name ?? 'Guru lain',
            'subject_name' => $s->subject?->name ?? '',
            'is_self'      => $s->teacher_id === $teacher->id,
        ])->values();

        return response()->json([
            'class_id' => (int) $request->class_id,
            'date'     => $request->date,
            'occupied' => $occupied,
        ]);
    }

    // POST /api/v1/guru/teaching-sessions
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date'       => 'required|date',
            'period'     => 'nullable|integer|min:1|max:12',
            'periods'    => 'nullable|array|min:1',
            'periods.*'  => 'integer|min:1|max:12',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
            'note'       => 'nullable|string|max:255',
            'attendances'=> 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status'     => 'required|in:hadir,tidak_hadir,izin,sakit,dispensasi,alpa',
        ]);

        $teacher = Auth::user();

        // Ambil daftar period yang dipilih (bisa array periods atau single period)
        $periods = $request->input('periods');
        if (empty($periods) && $request->filled('period')) {
            $periods = [(int) $request->input('period')];
        }

        if (empty($periods)) {
            return response()->json(['message' => 'Pilih minimal satu jam pelajaran.'], 422);
        }

        // Cek konflik: apakah jam yang dipilih sudah diisi oleh guru LAIN pada kelas dan tanggal yang sama?
        $conflicts = TeacherAttendance::where('class_id', $request->class_id)
            ->whereDate('date', $request->date)
            ->whereIn('period', $periods)
            ->where('teacher_id', '!=', $teacher->id)
            ->with('teacher:id,name')
            ->get();

        if ($conflicts->isNotEmpty()) {
            $details = $conflicts->map(fn ($c) => "Jam {$c->period} ({$c->teacher?->name})")->join(', ');
            return response()->json([
                'message' => "Gagal menyimpan: {$details} sudah diisi oleh guru lain di kelas ini.",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $createdIds = [];

            foreach ($periods as $p) {
                $session = TeacherAttendance::updateOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'class_id'   => $request->class_id,
                        'date'       => $request->date,
                        'period'     => $p,
                    ],
                    [
                        'subject_id' => $request->subject_id,
                        'start_time' => $request->start_time,
                        'end_time'   => $request->end_time,
                        'status'     => 'hadir',
                        'note'       => $request->note,
                    ]
                );

                foreach ($request->attendances as $att) {
                    SessionAttendance::updateOrCreate(
                        [
                            'student_id' => $att['student_id'],
                            'date'       => $request->date,
                            'period'     => $p,
                        ],
                        [
                            'teacher_attendance_id' => $session->id,
                            'class_id'              => $request->class_id,
                            'subject_id'            => $request->subject_id,
                            'status'                => $att['status'],
                            'note'                  => $att['note'] ?? null,
                        ]
                    );
                }

                $createdIds[] = $session->id;
            }

            DB::commit();

            // Notifikasi ke Orangtua jika ada siswa Sakit, Izin, atau Alpa pada jam pelajaran ini
            $subjectName = \App\Models\Subject::find($request->subject_id)?->name ?? 'Mata Pelajaran';
            $periodStr   = implode(', ', $periods);
            foreach ($request->attendances as $att) {
                if (in_array($att['status'], ['sakit', 'izin', 'tidak_hadir'], true)) {
                    $student = User::find($att['student_id']);
                    if ($student) {
                        $statusLabel = match ($att['status']) {
                            'sakit'       => 'SAKIT',
                            'izin'        => 'IZIN',
                            'tidak_hadir' => 'ALPA',
                            default       => strtoupper($att['status']),
                        };
                        NotificationService::notifyParentsOfStudent(
                            $student,
                            "Ketidakhadiran Jam Pelajaran — {$student->name}",
                            "Bpk/Ibu, {$student->name} dicatat {$statusLabel} pada Mata Pelajaran {$subjectName} (Jam ke-{$periodStr}) oleh Guru {$teacher->name}.",
                            'warning',
                            route('orangtua.attendance.index')
                        );
                    }
                }
            }

            $count = count($periods);
            return response()->json([
                'message' => "Absensi untuk {$count} jam pelajaran berhasil disimpan.",
                'ids'     => $createdIds,
                'id'      => $createdIds[0] ?? null,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    // GET /api/v1/guru/teaching-sessions/{id}
    public function show(int $id): JsonResponse
    {
        $teacher = Auth::user();
        $session = TeacherAttendance::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name', 'sessionAttendances.student:id,name,nis'])
            ->findOrFail($id);

        return response()->json($this->formatSession($session, withStudents: true));
    }

    // PUT /api/v1/guru/teaching-sessions/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $teacher = Auth::user();
        $session = TeacherAttendance::where('teacher_id', $teacher->id)->findOrFail($id);

        $validated = $request->validate([
            'materi'                   => 'nullable|string|max:255',
            'aktivitas'                => 'nullable|string',
            'catatan'                  => 'nullable|string',
            'note'                     => 'nullable|string',
            'tp_id'                    => 'nullable|integer|exists:tujuan_pembelajaran,id',
            'attendances'              => 'nullable|array',
            'attendances.*.student_id' => 'required_with:attendances|integer|exists:users,id',
            'attendances.*.status'     => 'required_with:attendances|string|in:hadir,sakit,izin,dispensasi,tidak_hadir,alpa',
            'attendances.*.note'       => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $session->update([
                'materi'    => $validated['materi'] ?? $session->materi,
                'aktivitas' => $validated['aktivitas'] ?? $session->aktivitas,
                'catatan'   => $validated['catatan'] ?? $validated['note'] ?? $session->catatan,
                'tp_id'     => $validated['tp_id'] ?? $session->tp_id,
            ]);

            if (isset($validated['attendances']) && is_array($validated['attendances'])) {
                foreach ($validated['attendances'] as $att) {
                    $status = $att['status'] === 'alpa' ? 'tidak_hadir' : $att['status'];
                    SessionAttendance::updateOrCreate(
                        [
                            'teacher_attendance_id' => $session->id,
                            'student_id'            => $att['student_id'],
                        ],
                        [
                            'status' => $status,
                            'note'   => $att['note'] ?? null,
                        ]
                    );
                }
            }

            DB::commit();

            $updatedSession = TeacherAttendance::with(['schoolClass:id,name', 'subject:id,name', 'sessionAttendances.student:id,name,nis'])
                ->find($session->id);

            return response()->json([
                'message' => 'Jurnal & absensi mengajar berhasil diperbarui.',
                'data'    => $this->formatSession($updatedSession, withStudents: true),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    // DELETE or POST /api/v1/guru/teaching-sessions/{id}/delete
    public function destroy(int $id): JsonResponse
    {
        /** @var \App\Models\User $teacher */
        $teacher = Auth::user();

        // 1. Check TeacherAttendance
        $session = TeacherAttendance::find($id);

        if (! $session) {
            $sessAtt = SessionAttendance::find($id);
            if ($sessAtt && $sessAtt->teacher_attendance_id) {
                $session = TeacherAttendance::find($sessAtt->teacher_attendance_id);
            }
        }

        if ($session) {
            $isOwner = (int) $session->teacher_id === (int) $teacher->id;
            $isStaff = in_array($teacher->role, ['admin', 'piket']) || $teacher->isBk();

            if (! $isOwner && ! $isStaff) {
                return response()->json(['message' => 'Anda tidak memiliki wewenang menghapus jurnal mengajar ini.'], 403);
            }

            DB::beginTransaction();
            try {
                $createdMinute = $session->created_at ? $session->created_at->format('Y-m-d H:i') : null;

                $siblingQuery = TeacherAttendance::where('teacher_id', $session->teacher_id)
                    ->where('class_id', $session->class_id)
                    ->where('subject_id', $session->subject_id)
                    ->whereDate('date', $session->date);

                if ($createdMinute) {
                    $siblingQuery->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [$createdMinute]);
                }

                $siblingSessions = $siblingQuery->get();
                $siblingIds = $siblingSessions->pluck('id')->toArray();

                if (empty($siblingIds)) {
                    $siblingIds = [$session->id];
                }

                SessionAttendance::whereIn('teacher_attendance_id', $siblingIds)->delete();
                TeacherAttendance::whereIn('id', $siblingIds)->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Jurnal mengajar berhasil dihapus.',
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
            }
        }

        // 2. Fallback check TeacherJournal
        $journal = TeacherJournal::find($id);
        if ($journal) {
            $isOwner = (int) $journal->teacher_id === (int) $teacher->id;
            $isStaff = in_array($teacher->role, ['admin', 'piket']) || $teacher->isBk();

            if (! $isOwner && ! $isStaff) {
                return response()->json(['message' => 'Anda tidak memiliki wewenang menghapus jurnal ini.'], 403);
            }

            DB::beginTransaction();
            try {
                $journal->absences()->delete();
                $journal->delete();

                DB::commit();

                return response()->json(['message' => 'Jurnal mengajar berhasil dihapus.']);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Jurnal mengajar telah dihapus.']);
    }

    // GET /api/v1/guru/teaching-sessions/class-students/{classId}?date=
    public function classStudents(Request $request, int $classId): JsonResponse
    {
        $teacher = Auth::user();

        // Validasi: guru boleh akses kelas ini?
        $hasAccess = $teacher->homeroomClass?->id === $classId
            || Schedule::where('teacher_id', $teacher->id)->where('class_id', $classId)->exists()
            || ! Schedule::where('teacher_id', $teacher->id)->exists();

        if (! $hasAccess) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $date = $request->input('date', today()->toDateString());

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        $studentIds  = $students->pluck('id')->toArray();
        $attendances = \App\Models\Attendance::whereIn('user_id', $studentIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        $result = $students->map(function ($s) use ($attendances) {
            $att = $attendances->get($s->id);
            $morningStatus = $att?->status;
            $isViaLupaAbsen = (bool) ($att?->via_lupa_absen ?? false);

            if ($isViaLupaAbsen || $morningStatus === 'lupa_absen') {
                $effectiveMorningStatus = 'lupa_absen';
                $morningStatusLabel = 'Lupa Absen';
            } elseif ($morningStatus === 'terlambat') {
                $effectiveMorningStatus = 'terlambat';
                $morningStatusLabel = 'Terlambat';
            } elseif ($morningStatus === 'hadir') {
                $effectiveMorningStatus = 'hadir';
                $morningStatusLabel = 'Hadir';
            } elseif ($morningStatus === 'sakit') {
                $effectiveMorningStatus = 'sakit';
                $morningStatusLabel = 'Sakit';
            } elseif ($morningStatus === 'izin') {
                $effectiveMorningStatus = 'izin';
                $morningStatusLabel = 'Izin';
            } elseif ($morningStatus === 'dispensasi') {
                $effectiveMorningStatus = 'dispensasi';
                $morningStatusLabel = 'Dispensasi';
            } elseif ($morningStatus === 'alpa') {
                $effectiveMorningStatus = 'alpa';
                $morningStatusLabel = 'Alpa';
            } else {
                $effectiveMorningStatus = 'belum_absen';
                $morningStatusLabel = 'Belum Absen Pagi';
            }

            $suggestedStatus = match ($effectiveMorningStatus) {
                'sakit'               => 'sakit',
                'izin'                => 'izin',
                'dispensasi'          => 'dispensasi',
                'alpa', 'belum_absen' => 'tidak_hadir',
                default               => 'hadir',
            };

            return [
                'id'                   => $s->id,
                'name'                 => $s->name,
                'nis'                  => $s->nis,
                'morning_status'       => $effectiveMorningStatus,
                'morning_status_label' => $morningStatusLabel,
                'suggested_status'     => $suggestedStatus,
                'via_lupa_absen'       => $isViaLupaAbsen,
            ];
        });

        return response()->json($result);
    }

    // GET /api/v1/guru/teaching-sessions/export?class_id=&month=&year=
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'month'    => 'required|integer|min:1|max:12',
            'year'     => 'required|integer',
        ]);

        $teacher = Auth::user();

        $sessions = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->with(['sessionAttendances.student:id,name,nis', 'subject:id,name'])
            ->orderBy('date')
            ->orderBy('period')
            ->get();

        $class = SchoolClass::find($request->class_id);
        $rows  = [];
        $rows[] = ['Tanggal', 'Jam Ke', 'Mata Pelajaran', 'NIS', 'Nama Siswa', 'Status'];

        foreach ($sessions as $s) {
            foreach ($s->sessionAttendances as $att) {
                $rows[] = [
                    $s->date->format('d/m/Y'),
                    $s->period,
                    $s->subject?->name ?? '—',
                    $att->student?->nis ?? '—',
                    $att->student?->name ?? '—',
                    $att->status,
                ];
            }
        }

        // Kembalikan data JSON untuk di-export menjadi CSV di client
        return response()->json([
            'filename' => "absensi_{$class?->name}_{$request->month}_{$request->year}.csv",
            'rows'     => $rows,
        ]);
    }

    private function formatSession(TeacherAttendance $s, bool $withStudents = false): array
    {
        $total  = $s->sessionAttendances->count();
        $hadir  = $s->sessionAttendances->where('status', 'hadir')->count();
        $alpha  = $s->sessionAttendances->where('status', 'tidak_hadir')->count();

        $data = [
            'id'           => $s->id,
            'class_id'     => $s->class_id,
            'class_name'   => $s->schoolClass?->name ?? '—',
            'subject_id'   => $s->subject_id,
            'subject_name' => $s->subject?->name ?? '—',
            'date'         => $s->date?->format('Y-m-d'),
            'period'       => $s->period,
            'start_time'   => $s->start_time,
            'end_time'     => $s->end_time,
            'status'       => $s->status,
            'note'         => $s->note,
            'total'        => $total,
            'hadir'        => $hadir,
            'alpha'        => $alpha,
        ];

        if ($withStudents) {
            $data['students'] = $s->sessionAttendances->map(fn ($att) => [
                'student_id'   => $att->student_id,
                'name'         => $att->student?->name ?? '—',
                'nis'          => $att->student?->nis ?? '—',
                'status'       => $att->status,
                'note'         => $att->note,
            ])->values();
        }

        return $data;
    }
}
