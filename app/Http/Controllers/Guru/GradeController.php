<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $guru */
        $guru = Auth::user();

        $classes         = SchoolClass::orderBy('name')->get();
        $selectedClassId = $request->get('class_id', $guru->homeroomClass?->id ?? $classes->first()?->id);
        $academicYear    = $request->get('academic_year', StudentGrade::currentAcademicYear());
        $semester        = $request->integer('semester', StudentGrade::currentSemester());

        $students = User::where('role', 'siswa')
            ->where('class_id', $selectedClassId)
            ->orderBy('name')
            ->get();

        $subjects = Subject::orderBy('name')->get();

        $grades = StudentGrade::with(['student', 'subject'])
            ->whereIn('student_id', $students->pluck('id'))
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->orderBy('subject_id')
            ->orderBy('type')
            ->get()
            ->groupBy('student_id');

        $academicYears = collect();
        $year = now()->year;
        for ($i = 0; $i <= 2; $i++) {
            $y = $year - $i;
            $academicYears->push($y . '/' . ($y + 1));
        }

        return view('guru.grades.index', compact(
            'classes', 'selectedClassId', 'students', 'subjects',
            'grades', 'academicYear', 'semester', 'academicYears'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id'    => 'required|exists:users,id',
            'subject_id'    => 'required|exists:subjects,id',
            'type'          => 'required|in:UH,UTS,UAS',
            'score'         => 'required|numeric|min:0|max:100',
            'semester'      => 'required|in:1,2',
            'academic_year' => 'required|string|max:9',
            'notes'         => 'nullable|string|max:200',
        ]);

        StudentGrade::updateOrCreate(
            [
                'student_id'    => $request->student_id,
                'subject_id'    => $request->subject_id,
                'type'          => $request->type,
                'semester'      => $request->semester,
                'academic_year' => $request->academic_year,
            ],
            [
                'score'       => $request->score,
                'notes'       => $request->notes,
                'recorded_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    public function destroy(StudentGrade $grade): RedirectResponse
    {
        $grade->delete();
        return back()->with('success', 'Nilai berhasil dihapus.');
    }
}
