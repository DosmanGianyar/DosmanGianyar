<?php

namespace App\Http\Controllers\Guru;

use App\Exports\AttendanceExport;
use App\Exports\AttendanceGridExport;
use App\Exports\ConductLogExport;
use App\Exports\StudentGradeExport;
use App\Exports\TeacherAttendanceExport;
use Carbon\Carbon;
use App\Models\TeacherAttendance;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\ConductLog;
use App\Models\DamageReport;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    // ─── Absensi ──────────────────────────────────────────────────────────────

    public function attendanceForm(): \Illuminate\View\View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Admin dan guru BK bisa akses semua kelas; guru biasa hanya kelas wali-nya
        $classes = ($user->isAdmin() || $user->isBk())
            ? SchoolClass::orderBy('name')->get()
            : SchoolClass::where('homeroom_teacher_id', $user->id)->orderBy('name')->get();

        $teachers = User::where('role', 'guru')->orderBy('name')->get();
        return view('guru.exports.attendance-form', compact('classes', 'teachers'));
    }

    public function attendanceExcel(Request $request): BinaryFileResponse
    {
        $request->validate([
            'month'    => 'required|date_format:Y-m',
            'class_id' => 'nullable|exists:classes,id',
            'status'   => 'nullable|in:hadir,terlambat,alpa,izin,sakit',
        ]);

        $filename = 'absensi_' . $request->month . ($request->class_id ? '_kelas' . $request->class_id : '') . '.xlsx';

        return Excel::download(
            new AttendanceExport($request->class_id, $request->month, $request->status),
            $filename
        );
    }

    public function attendancePdf(Request $request): Response
    {
        $request->validate([
            'month'    => 'required|date_format:Y-m',
            'class_id' => 'nullable|exists:classes,id',
            'status'   => 'nullable|in:hadir,terlambat,alpa,izin,sakit',
        ]);

        [$year, $mon] = explode('-', $request->month);

        $query = Attendance::with(['student.schoolClass'])
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->whereHas('student', fn($q) => $q->where('role', 'siswa'));

        if ($request->class_id) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $records   = $query->orderBy('date')->orderBy('user_id')->get();
        $className = $request->class_id ? SchoolClass::find($request->class_id)?->name : null;

        $pdf = Pdf::loadView('exports.attendance-pdf', [
            'records'      => $records,
            'month'        => $request->month,
            'className'    => $className,
            'statusFilter' => $request->status,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('absensi_' . $request->month . '.pdf');
    }

    // ─── Absensi Grid (Rekap Bulanan) ────────────────────────────────────────

    private function authorizeClassAccess(int $classId): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if ($user->isAdmin() || $user->isBk()) return;

        $isHomeroom = SchoolClass::where('id', $classId)
            ->where('homeroom_teacher_id', $user->id)
            ->exists();

        abort_unless($isHomeroom, 403, 'Anda hanya dapat mengekspor kelas yang Anda ampu sebagai wali kelas.');
    }

    public function attendanceGridExcel(Request $request): BinaryFileResponse
    {
        $request->validate([
            'month'    => 'required|date_format:Y-m',
            'class_id' => 'required|exists:classes,id',
        ]);

        $this->authorizeClassAccess((int) $request->class_id);

        $className = SchoolClass::find($request->class_id)?->name;
        $filename  = 'rekap_absensi_' . $className . '_' . $request->month . '.xlsx';

        return Excel::download(
            new AttendanceGridExport($request->month, (int) $request->class_id),
            $filename
        );
    }

    public function attendanceGridPdf(Request $request): Response
    {
        $request->validate([
            'month'    => 'required|date_format:Y-m',
            'class_id' => 'required|exists:classes,id',
        ]);

        $this->authorizeClassAccess((int) $request->class_id);

        [$year, $mon] = explode('-', $request->month);
        $start        = Carbon::parse("$year-$mon-01");
        $daysInMonth  = $start->daysInMonth;

        $students = User::where('role', 'siswa')
            ->where('class_id', $request->class_id)
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        // Build grid: $grid[$studentId][$dayNumber] = status|null
        $records = Attendance::whereHas('student', fn($q) => $q->where('class_id', $request->class_id))
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->get()
            ->groupBy('user_id')
            ->map(fn($g) => $g->keyBy(fn($r) => (int) $r->date->format('j')));

        $grid = [];
        foreach ($students as $student) {
            $dayMap = $records->get($student->id, collect());
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $grid[$student->id][$d] = $dayMap->get($d)?->status ?? null;
            }
        }

        $className = SchoolClass::find($request->class_id)?->name;
        $filename  = 'rekap_absensi_' . $className . '_' . $request->month . '.pdf';

        $html = view('exports.attendance-grid-pdf', [
            'students'  => $students,
            'grid'      => $grid,
            'month'     => $request->month,
            'className' => $className,
        ])->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->landscape()
            ->margins(15, 12, 12, 12)
            ->waitUntilNetworkIdle()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── Absensi Guru ─────────────────────────────────────────────────────────

    public function teacherAttendanceExcel(Request $request): BinaryFileResponse
    {
        $request->validate([
            'month'      => 'required|date_format:Y-m',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $filename = 'absensi_guru_' . $request->month . '.xlsx';
        return Excel::download(
            new TeacherAttendanceExport($request->month, $request->teacher_id),
            $filename
        );
    }

    public function teacherAttendancePdf(Request $request): Response
    {
        $request->validate([
            'month'      => 'required|date_format:Y-m',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        [$year, $mon] = explode('-', $request->month);

        $query = TeacherAttendance::with(['teacher', 'schoolClass', 'subject'])
            ->whereYear('date', $year)
            ->whereMonth('date', $mon);

        if ($request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $records     = $query->orderBy('date')->orderBy('teacher_id')->orderBy('period')->get();
        $teacherName = $request->teacher_id ? User::find($request->teacher_id)?->name : null;

        $pdf = Pdf::loadView('exports.teacher-attendance-pdf', [
            'records'     => $records,
            'month'       => $request->month,
            'teacherName' => $teacherName,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('absensi_guru_' . $request->month . '.pdf');
    }

    // ─── Poin Perilaku ────────────────────────────────────────────────────────

    public function conductForm(): \Illuminate\View\View
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('guru.exports.conduct-form', compact('classes'));
    }

    public function conductExcel(Request $request): BinaryFileResponse
    {
        $request->validate([
            'month'    => 'required|date_format:Y-m',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $filename = 'poin_' . $request->month . '.xlsx';
        return Excel::download(new ConductLogExport($request->class_id, $request->month), $filename);
    }

    public function conductPdf(Request $request): Response
    {
        $request->validate([
            'month'    => 'required|date_format:Y-m',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        [$year, $mon] = explode('-', $request->month);

        $query = ConductLog::with(['student.schoolClass', 'teacher', 'category'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $mon);

        if ($request->class_id) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        }

        $records   = $query->orderBy('created_at')->get();
        $className = $request->class_id ? SchoolClass::find($request->class_id)?->name : null;

        $pdf = Pdf::loadView('exports.conduct-pdf', [
            'records'   => $records,
            'month'     => $request->month,
            'className' => $className,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('poin_perilaku_' . $request->month . '.pdf');
    }

    // ─── Sarpras ──────────────────────────────────────────────────────────────

    public function sarprasForm(): \Illuminate\View\View
    {
        $rooms = Room::orderBy('name')->get();
        return view('guru.exports.sarpras-form', compact('rooms'));
    }

    public function assetsPdf(Request $request): Response
    {
        $request->validate([
            'room_id'    => 'nullable|exists:rooms,id',
            'condition'  => 'nullable|in:baik,rusak_ringan,rusak_berat',
            'category'   => 'nullable|string|max:50',
        ]);

        $query = Asset::with('room');

        if ($request->room_id)   $query->where('room_id', $request->room_id);
        if ($request->condition) $query->where('condition', $request->condition);
        if ($request->category)  $query->where('category', $request->category);

        $assets   = $query->orderBy('name')->get();
        $roomName = $request->room_id ? Room::find($request->room_id)?->name : null;

        $pdf = Pdf::loadView('exports.assets-pdf', compact('assets', 'roomName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan_aset_' . now()->format('Ymd') . '.pdf');
    }

    public function damagePdf(Request $request): Response
    {
        $request->validate([
            'status' => 'nullable|in:pending,in_progress,resolved',
        ]);

        $query = DamageReport::with(['asset.room', 'reporter', 'handler']);

        if ($request->status) $query->where('status', $request->status);

        $reports = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('exports.damage-pdf', compact('reports'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan_kerusakan_' . now()->format('Ymd') . '.pdf');
    }

    // ─── Nilai Siswa ──────────────────────────────────────────────────────────

    public function gradesForm(): \Illuminate\View\View
    {
        $classes      = SchoolClass::orderBy('name')->get();
        $subjects     = Subject::orderBy('name')->get();
        $currentYear  = StudentGrade::currentAcademicYear();
        $currentSem   = StudentGrade::currentSemester();
        return view('guru.exports.grades-form', compact('classes', 'subjects', 'currentYear', 'currentSem'));
    }

    public function gradesPdf(Request $request): Response
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|in:1,2',
            'academic_year' => 'required|string|max:9',
        ]);

        $students = User::where('role', 'siswa')
            ->where('class_id', $request->class_id)
            ->orderBy('name')
            ->get();

        $grades = StudentGrade::with('subject')
            ->whereIn('student_id', $students->pluck('id'))
            ->where('semester', $request->semester)
            ->where('academic_year', $request->academic_year)
            ->orderBy('subject_id')
            ->orderBy('type')
            ->get()
            ->groupBy('student_id');

        $subjects = Subject::whereIn('id',
            $grades->flatten()->pluck('subject_id')->unique()
        )->orderBy('name')->get();

        $className = SchoolClass::find($request->class_id)?->name;

        $pdf = Pdf::loadView('exports.grades-pdf', [
            'students'     => $students,
            'grades'       => $grades,
            'subjects'     => $subjects,
            'className'    => $className,
            'semester'     => $request->semester,
            'academicYear' => $request->academic_year,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('nilai_' . $className . '_sem' . $request->semester . '_' . str_replace('/', '-', $request->academic_year) . '.pdf');
    }

    public function gradesExcel(Request $request): BinaryFileResponse
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|in:1,2',
            'academic_year' => 'required|string|max:9',
        ]);

        $filename = 'nilai_kelas_sem' . $request->semester . '_' . str_replace('/', '-', $request->academic_year) . '.xlsx';

        return Excel::download(
            new StudentGradeExport($request->class_id, $request->semester, $request->academic_year),
            $filename
        );
    }
}
