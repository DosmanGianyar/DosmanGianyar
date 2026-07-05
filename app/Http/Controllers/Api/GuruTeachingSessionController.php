<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SessionAttendance;
use App\Models\TeacherAttendance;
use App\Models\User;
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
            ->with(['schoolClass:id,name', 'subject:id,name', 'sessionAttendances'])
            ->orderByDesc('date')
            ->orderByDesc('period');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }

        $sessions = $query->paginate(20);

        return response()->json([
            'data' => $sessions->map(fn ($s) => $this->formatSession($s)),
            'meta' => [
                'current_page' => $sessions->currentPage(),
                'last_page'    => $sessions->lastPage(),
                'total'        => $sessions->total(),
            ],
        ]);
    }

    // POST /api/v1/guru/teaching-sessions
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date'       => 'required|date',
            'period'     => 'required|integer|min:1|max:12',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
            'note'       => 'nullable|string|max:255',
            'attendances'=> 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status'     => 'required|in:hadir,tidak_hadir,izin,sakit',
        ]);

        $teacher = Auth::user();

        DB::beginTransaction();
        try {
            $session = TeacherAttendance::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'class_id'   => $request->class_id,
                    'date'       => $request->date,
                    'period'     => $request->period,
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
                        'period'     => $request->period,
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

            DB::commit();
            return response()->json(['message' => 'Absensi berhasil disimpan.', 'id' => $session->id], 201);
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

    // GET /api/v1/guru/teaching-sessions/class-students/{classId}
    public function classStudents(int $classId): JsonResponse
    {
        $teacher = Auth::user();

        // Validasi: guru boleh akses kelas ini?
        $hasAccess = $teacher->homeroomClass?->id === $classId
            || Schedule::where('teacher_id', $teacher->id)->where('class_id', $classId)->exists();

        if (! $hasAccess) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        return response()->json($students);
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
