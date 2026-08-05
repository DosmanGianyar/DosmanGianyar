<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherJournal;
use App\Models\TeacherJournalAbsence;
use App\Models\TujuanPembelajaran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use App\Models\Schedule;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user();
        $month   = $request->integer('month', now()->month);
        $year    = $request->integer('year', now()->year);
        $classId = $request->input('class_id');

        $query = TeacherJournal::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name', 'tp:id,code,description', 'absences'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderByDesc('date')
            ->orderByDesc('period');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $journals = $query->get();
        $classes  = SchoolClass::orderBy('name')->get();
        $total    = $journals->count();

        return view('guru.journal.index', compact('journals', 'classes', 'month', 'year', 'classId', 'total'));
    }

    public function create(Request $request): View
    {
        $teacher  = Auth::user();
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $dayOfWeek = now()->dayOfWeek; // 0=Sun, 1=Mon...6=Sat
        $scheduleDay = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? $dayOfWeek : null;

        $todaySchedules = [];
        if ($scheduleDay) {
            $schedules = Schedule::where('teacher_id', $teacher->id)
                ->where('day', $scheduleDay)
                ->orderBy('period')
                ->get();

            foreach ($schedules as $sch) {
                $todaySchedules[$sch->class_id] = [
                    'period'     => $sch->period,
                    'subject_id' => $sch->subject_id,
                ];
            }
        }

        $preClassId   = $request->input('class_id');
        $preSubjectId = $request->input('subject_id');
        $prePeriod    = $request->input('period');

        $mySubjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

        $tps = TujuanPembelajaran::where(function ($q) use ($teacher, $mySubjectIds) {
                $q->where('teacher_id', $teacher->id);
                if (count($mySubjectIds)) {
                    $q->orWhereIn('subject_id', $mySubjectIds);
                }
            })
            ->where('is_active', true)
            ->with('subject:id,name')
            ->orderBy('subject_id')
            ->orderByDesc('id')
            ->get();

        return view('guru.journal.create', compact('classes', 'subjects', 'tps', 'todaySchedules', 'preClassId', 'preSubjectId', 'prePeriod'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'class_id'                     => 'required|exists:classes,id',
            'subject_id'                   => 'nullable|exists:subjects,id',
            'date'                         => 'required|date',
            'period'                       => 'nullable|integer|min:1|max:12',
            'period_end'                   => 'nullable|integer|min:1|max:12|gte:period',
            'tp_id'                        => 'nullable|exists:tujuan_pembelajaran,id',
            'material'                     => 'required|string|max:1000',
            'activity'                     => 'required|string|max:1000',
            'notes'                        => 'nullable|string|max:500',
            'absent_students'              => 'nullable|array',
            'absent_students.*.student_id' => 'required|exists:users,id',
            'absent_students.*.status'     => 'required|string|in:tidak_hadir,izin,sakit,dispensasi,alpa',
        ]);

        $teacher = Auth::user();

        $lo = null;
        if ($request->filled('tp_id')) {
            $mySubjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();
            $tp = TujuanPembelajaran::where(function ($q) use ($teacher, $mySubjectIds) {
                    $q->where('teacher_id', $teacher->id);
                    if (count($mySubjectIds)) {
                        $q->orWhereIn('subject_id', $mySubjectIds);
                    }
                })
                ->find($request->tp_id);
            if ($tp) {
                $lo = ($tp->code ? "[{$tp->code}] " : '') . $tp->description;
            }
        }

        DB::beginTransaction();
        try {
            $journal = TeacherJournal::create([
                'teacher_id'          => $teacher->id,
                'class_id'            => $request->class_id,
                'subject_id'          => $request->subject_id ?: null,
                'tp_id'               => $request->tp_id ?: null,
                'date'                => $request->date,
                'period'              => $request->period ?: null,
                'period_end'          => $request->period_end ?: null,
                'learning_objectives' => $lo,
                'material'            => $request->material,
                'activity'            => $request->activity,
                'notes'               => $request->notes ?: null,
            ]);

            foreach ($request->input('absent_students', []) as $abs) {
                if (!empty($abs['student_id']) && !empty($abs['status'])) {
                    TeacherJournalAbsence::create([
                        'journal_id' => $journal->id,
                        'student_id' => $abs['student_id'],
                        'status'     => $abs['status'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('guru.journal.index')
                ->with('success', 'Jurnal mengajar berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function print(Request $request): View
    {
        $teacher = Auth::user();
        $month   = $request->integer('month', now()->month);
        $year    = $request->integer('year', now()->year);
        $classId = $request->input('class_id');

        $query = TeacherJournal::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name', 'tp:id,code,description', 'absences.student:id,name,nis'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->orderBy('period');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $journals  = $query->get();
        $classes   = SchoolClass::orderBy('name')->get();
        $className = $classId ? SchoolClass::find($classId)?->name : null;

        return view('guru.journal.print', compact(
            'teacher', 'journals', 'classes',
            'month', 'year', 'classId', 'className'
        ));
    }

    public function printWeekly(Request $request): View
    {
        $teacher = $request->filled('teacher_id')
            ? (User::where('role', 'guru')->find($request->input('teacher_id')) ?: Auth::user())
            : Auth::user();
        $classId  = $request->input('class_id');
        $weekDate = $request->input('week_date', now()->toDateString());

        $carbonDate  = \Illuminate\Support\Carbon::parse($weekDate);
        $startOfWeek = $carbonDate->copy()->startOfWeek(\Illuminate\Support\Carbon::MONDAY);
        $endOfWeek   = $carbonDate->copy()->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->addDays(5); // Senin - Sabtu

        $query = TeacherJournal::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name', 'tp:id,code,description', 'absences.student:id,name,nis'])
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->orderBy('date')
            ->orderBy('period');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $journals  = $query->get();
        $classes   = SchoolClass::orderBy('name')->get();
        $teachers  = User::where('role', 'guru')->orderBy('name')->get(['id', 'name']);
        $className = $classId ? SchoolClass::find($classId)?->name : null;

        return view('guru.journal.print_weekly', compact(
            'teacher', 'journals', 'classes', 'teachers', 'classId', 'className',
            'startOfWeek', 'endOfWeek', 'weekDate'
        ));
    }

    public function printWeeklyAttendance(Request $request): View
    {
        $teacher = $request->filled('teacher_id')
            ? (User::where('role', 'guru')->find($request->input('teacher_id')) ?: Auth::user())
            : Auth::user();
        $classes  = SchoolClass::orderBy('name')->get();
        $classId  = $request->input('class_id') ?: ($classes->first()?->id);
        $weekDate = $request->input('week_date', now()->toDateString());

        $carbonDate  = \Illuminate\Support\Carbon::parse($weekDate);
        $startOfWeek = $carbonDate->copy()->startOfWeek(\Illuminate\Support\Carbon::MONDAY);
        $endOfWeek   = $carbonDate->copy()->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->addDays(5); // Senin - Sabtu

        $selectedClass = $classId ? SchoolClass::find($classId) : null;
        $students      = $selectedClass
            ? User::where('role', 'siswa')->where('class_id', $selectedClass->id)->orderBy('name')->get(['id', 'name', 'nis'])
            : collect();

        // Get journals for this class and date range
        $journals = TeacherJournal::where('class_id', $classId)
            ->with(['absences'])
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get();

        // Get morning gate attendances for these students for this week
        $studentIds = $students->pluck('id')->toArray();
        $morningAttendances = \App\Models\Attendance::whereIn('user_id', $studentIds)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get()
            ->groupBy(fn ($att) => $att->user_id . '_' . $att->date->format('Y-m-d'));

        // Map dates: 6 days (Senin - Sabtu)
        $days = [];
        for ($i = 0; $i < 6; $i++) {
            $dt = $startOfWeek->copy()->addDays($i);
            $days[] = [
                'date_str'  => $dt->toDateString(),
                'day_name'  => $dt->isoFormat('dddd'),
                'day_short' => $dt->isoFormat('ddd, D/M'),
            ];
        }

        // Map student weekly matrix
        $attendanceMatrix = $students->map(function ($s) use ($days, $journals, $morningAttendances) {
            $row = [
                'student' => $s,
                'days'    => [],
                'sakit'   => 0,
                'izin'    => 0,
                'alpa'    => 0,
                'dispen'  => 0,
                'hadir'   => 0,
            ];

            foreach ($days as $day) {
                $dateStr = $day['date_str'];
                
                // Check journal absence for this student on dateStr
                $absRecord = null;
                foreach ($journals->where('date', $dateStr) as $j) {
                    $found = $j->absences->firstWhere('student_id', $s->id);
                    if ($found) {
                        $absRecord = $found;
                        break;
                    }
                }

                if ($absRecord) {
                    $st = match ($absRecord->status) {
                        'sakit'               => 'S',
                        'izin'                => 'I',
                        'dispensasi'          => 'D',
                        'alpa', 'tidak_hadir' => 'A',
                        default               => 'H',
                    };
                } else {
                    // Check morning attendance
                    $key = $s->id . '_' . $dateStr;
                    $mAttList = $morningAttendances->get($key);
                    $mAtt = $mAttList ? $mAttList->first() : null;
                    if ($mAtt && in_array($mAtt->status, ['sakit', 'izin', 'dispensasi', 'alpa'])) {
                        $st = match ($mAtt->status) {
                            'sakit'      => 'S',
                            'izin'       => 'I',
                            'dispensasi' => 'D',
                            'alpa'       => 'A',
                            default      => 'H',
                        };
                    } else {
                        $st = 'H';
                    }
                }

                // Count stats
                match ($st) {
                    'S' => $row['sakit']++,
                    'I' => $row['izin']++,
                    'A' => $row['alpa']++,
                    'D' => $row['dispen']++,
                    default => $row['hadir']++,
                };

                $row['days'][$dateStr] = $st;
            }

            return $row;
        });

        $teachers = User::where('role', 'guru')->orderBy('name')->get(['id', 'name']);

        return view('guru.journal.print_weekly_attendance', compact(
            'teacher', 'classes', 'teachers', 'classId', 'selectedClass',
            'startOfWeek', 'endOfWeek', 'weekDate', 'days', 'attendanceMatrix'
        ));
    }

    public function destroy(TeacherJournal $journal): RedirectResponse
    {
        abort_unless($journal->teacher_id === Auth::id(), 403, 'Akses ditolak.');
        $journal->delete();
        return back()->with('success', 'Jurnal berhasil dihapus.');
    }

    public function studentsByClass(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $date    = $request->input('date', today()->toDateString());

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        $studentIds = $students->pluck('id')->toArray();
        $attendances = \App\Models\Attendance::whereIn('user_id', $studentIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        $result = $students->map(function ($s) use ($attendances) {
            $att = $attendances->get($s->id);
            $morningStatus = $att?->status;

            // Map morning status to suggested journal absence status
            $suggestedStatus = match ($morningStatus) {
                'sakit'      => 'sakit',
                'izin'       => 'izin',
                'dispensasi' => 'dispensasi',
                'alpa'       => 'alpa',
                default      => 'hadir',
            };

            return [
                'id'                   => $s->id,
                'name'                 => $s->name,
                'nis'                  => $s->nis,
                'morning_status'       => $morningStatus,
                'morning_status_label' => $morningStatus ? ucfirst($morningStatus) : 'Belum Absen Pagi',
                'suggested_status'     => $suggestedStatus,
            ];
        });

        return response()->json($result);
    }
}
