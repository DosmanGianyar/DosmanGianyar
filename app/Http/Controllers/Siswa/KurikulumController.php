<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\Schedule;
use App\Models\StudentGrade;
use App\Models\StudentHomeroomTeacher;
use App\Models\Subject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KurikulumController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $siswa->load('schoolClass');
        $classId = $siswa->class_id;

        // ISO weekday: 1=Monday … 7=Sunday; sekolah hanya 1–5
        $todayIso = now()->dayOfWeekIso;
        $isWeekday = $todayIso >= 1 && $todayIso <= 5;

        $todaySchedule = $isWeekday
            ? Schedule::where('class_id', $classId)
                ->where('day', $todayIso)
                ->with(['subject', 'teacher'])
                ->orderBy('period')
                ->get()
            : collect();

        $weekSchedule = Schedule::where('class_id', $classId)
            ->with(['subject', 'teacher'])
            ->orderBy('day')
            ->orderBy('period')
            ->get()
            ->groupBy('day');

        $upcomingEvents = AcademicEvent::where('end_date', '>=', today())
            ->orderBy('start_date')
            ->limit(30)
            ->get();

        $dayNames = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $academicYear = StudentGrade::currentAcademicYear();
        $semester     = StudentGrade::currentSemester();

        $grades = StudentGrade::with('subject')
            ->where('student_id', $siswa->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->orderBy('subject_id')
            ->orderBy('type')
            ->get()
            ->groupBy('subject_id');

        $guruWali = StudentHomeroomTeacher::where('student_id', $siswa->id)
            ->with('teacher:id,name,subject')
            ->first()
            ?->teacher;

        return view('siswa.kurikulum.index', compact(
            'siswa', 'todaySchedule', 'weekSchedule',
            'upcomingEvents', 'dayNames', 'todayIso', 'isWeekday',
            'grades', 'academicYear', 'semester', 'guruWali'
        ));
    }

    public function rapor(): Response
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user()->load('schoolClass');

        $academicYear = StudentGrade::currentAcademicYear();
        $semester     = StudentGrade::currentSemester();

        $gradeRecords = StudentGrade::with('subject')
            ->where('student_id', $siswa->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->orderBy('subject_id')
            ->orderBy('type')
            ->get()
            ->groupBy('subject_id');

        $subjects = Subject::whereIn('id', $gradeRecords->keys())->orderBy('name')->get();

        $pdf = Pdf::loadView('siswa.kurikulum.rapor-pdf', [
            'siswa'        => $siswa,
            'grades'       => $gradeRecords,
            'subjects'     => $subjects,
            'academicYear' => $academicYear,
            'semester'     => $semester,
        ])->setPaper('a4', 'portrait');

        $filename = 'rapor_' . str_replace(' ', '_', $siswa->name) . '_sem' . $semester . '.pdf';

        return $pdf->download($filename);
    }
}
