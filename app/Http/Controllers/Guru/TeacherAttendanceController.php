<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SessionAttendance;
use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $guru = auth()->user();
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Day of week: Carbon uses 0=Sunday…6=Saturday, schedules use 1=Mon…5=Fri
        $dayOfWeek = $date->dayOfWeek; // 0=Sun, 1=Mon…6=Sat
        $scheduleDay = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? $dayOfWeek : null;

        $schedules = collect();
        if ($scheduleDay) {
            $schedules = Schedule::with(['schoolClass', 'subject'])
                ->where('teacher_id', $guru->id)
                ->where('day', $scheduleDay)
                ->orderBy('period')
                ->get();
        }

        // Existing attendance records for this teacher/date
        $existing = TeacherAttendance::where('teacher_id', $guru->id)
            ->where('date', $date->toDateString())
            ->get()
            ->keyBy(fn($r) => $r->schedule_id ?? ($r->period . '_' . $r->class_id));

        // History — last 30 days
        $history = TeacherAttendance::with(['schoolClass', 'subject'])
            ->where('teacher_id', $guru->id)
            ->where('date', '>=', Carbon::today()->subDays(29))
            ->orderByDesc('date')
            ->orderBy('period')
            ->get();

        return view('guru.teacher-attendance.index', compact(
            'guru', 'date', 'schedules', 'existing', 'history', 'scheduleDay'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'date'        => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.schedule_id'       => 'nullable|exists:schedules,id',
            'attendances.*.class_id'          => 'required|exists:classes,id',
            'attendances.*.subject_id'        => 'nullable|exists:subjects,id',
            'attendances.*.period'            => 'required|integer|min:1|max:12',
            'attendances.*.start_time'        => 'nullable|date_format:H:i',
            'attendances.*.end_time'          => 'nullable|date_format:H:i',
            'attendances.*.status'            => 'required|in:hadir,tidak_hadir,izin,sakit',
            'attendances.*.note'              => 'nullable|string|max:255',
            'attendances.*.students'          => 'nullable|array',
            'attendances.*.students.*.id'     => 'required|exists:users,id',
            'attendances.*.students.*.status' => 'required|in:hadir,tidak_hadir,izin,sakit',
            'attendances.*.students.*.note'   => 'nullable|string|max:255',
        ]);

        $guru = auth()->user();

        foreach ($request->attendances as $item) {
            $session = TeacherAttendance::updateOrCreate(
                [
                    'teacher_id' => $guru->id,
                    'date'       => $request->date,
                    'period'     => $item['period'],
                    'class_id'   => $item['class_id'],
                ],
                [
                    'schedule_id' => $item['schedule_id'] ?? null,
                    'subject_id'  => $item['subject_id']  ?? null,
                    'start_time'  => $item['start_time']  ?? null,
                    'end_time'    => $item['end_time']    ?? null,
                    'status'      => $item['status'],
                    'note'        => $item['note']        ?? null,
                ]
            );

            foreach ($item['students'] ?? [] as $s) {
                SessionAttendance::updateOrCreate(
                    [
                        'student_id' => $s['id'],
                        'date'       => $request->date,
                        'period'     => $item['period'],
                    ],
                    [
                        'teacher_attendance_id' => $session->id,
                        'class_id'              => $item['class_id'],
                        'subject_id'            => $item['subject_id'] ?? null,
                        'status'                => $s['status'],
                        'note'                  => $s['note'] ?? null,
                    ]
                );
            }
        }

        return redirect()->route('guru.teacher-attendance.index', ['date' => $request->date])
            ->with('success', 'Absensi mengajar berhasil disimpan.');
    }

    public function studentsByClass(Request $request): JsonResponse
    {
        $classId = $request->integer('class_id');
        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        return response()->json($students);
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $request->validate([
            'date'       => 'required|date',
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'period'     => 'required|integer|min:1|max:12',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
            'status'     => 'required|in:hadir,tidak_hadir,izin,sakit',
            'note'       => 'nullable|string|max:255',
            'students'            => 'nullable|array',
            'students.*.id'       => 'required|exists:users,id',
            'students.*.status'   => 'required|in:hadir,tidak_hadir,izin,sakit',
            'students.*.note'     => 'nullable|string|max:255',
        ]);

        $guru = auth()->user();

        $session = TeacherAttendance::updateOrCreate(
            [
                'teacher_id' => $guru->id,
                'date'       => $request->date,
                'period'     => $request->period,
                'class_id'   => $request->class_id,
            ],
            [
                'schedule_id' => null,
                'subject_id'  => $request->subject_id,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'status'      => $request->status,
                'note'        => $request->note,
            ]
        );

        // Save per-student attendance for this session
        foreach ($request->input('students', []) as $item) {
            SessionAttendance::updateOrCreate(
                [
                    'student_id' => $item['id'],
                    'date'       => $request->date,
                    'period'     => $request->period,
                ],
                [
                    'teacher_attendance_id' => $session->id,
                    'class_id'              => $request->class_id,
                    'subject_id'            => $request->subject_id,
                    'status'                => $item['status'],
                    'note'                  => $item['note'] ?? null,
                ]
            );
        }

        return redirect()->route('guru.teacher-attendance.index', ['date' => $request->date])
            ->with('success', 'Sesi manual berhasil ditambahkan.');
    }

    public function destroy(TeacherAttendance $teacherAttendance): RedirectResponse
    {
        abort_unless($teacherAttendance->teacher_id === auth()->id(), 403);

        $date = $teacherAttendance->date->toDateString();
        $teacherAttendance->delete();

        return redirect()->route('guru.teacher-attendance.index', ['date' => $date])
            ->with('success', 'Catatan absensi berhasil dihapus.');
    }
}
