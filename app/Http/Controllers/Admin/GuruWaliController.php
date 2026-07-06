<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeroomConsultation;
use App\Models\SchoolClass;
use App\Models\StudentHomeroomTeacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuruWaliController extends Controller
{
    public function index(): View
    {
        $teachers = User::where('role', 'guru')
            ->withCount('waliStudents as student_count')
            ->orderBy('name')
            ->get();

        return view('admin.guru-wali.index', compact('teachers'));
    }

    public function show(User $teacher): View
    {
        abort_unless($teacher->role === 'guru', 404);

        $assignedRecords = StudentHomeroomTeacher::where('teacher_id', $teacher->id)
            ->with('student.schoolClass')
            ->get();

        $assignedIds = $assignedRecords->pluck('student_id');

        $availableStudents = User::whereIn('role', ['siswa', 'pengelola'])
            ->whereNotIn('id', $assignedIds)
            ->whereDoesntHave('homeroomTeacherRecord')
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        $consultations = HomeroomConsultation::where('teacher_id', $teacher->id)
            ->with('student:id,name,nis')
            ->latest()
            ->get();

        $counts = $consultations->groupBy('status')->map->count();

        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.guru-wali.show', compact(
            'teacher', 'assignedRecords', 'availableStudents', 'consultations', 'counts', 'classes'
        ));
    }

    public function assign(Request $request, User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === 'guru', 404);

        $request->validate(['student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id']);

        $assignedNames = [];
        $skippedNames  = [];

        foreach ($request->student_ids as $studentId) {
            $student = User::find($studentId);
            if (! $student || ! $student->isSiswa()) continue;

            if (StudentHomeroomTeacher::where('student_id', $studentId)->exists()) {
                $skippedNames[] = $student->name;
                continue;
            }

            StudentHomeroomTeacher::create([
                'student_id'  => $studentId,
                'teacher_id'  => $teacher->id,
                'assigned_at' => now(),
            ]);
            $assignedNames[] = $student->name;
        }

        if (empty($assignedNames)) {
            return back()->with('error', 'Tidak ada siswa yang ditugaskan (semua sudah memiliki Guru Wali).');
        }

        $message = count($assignedNames) . ' siswa berhasil ditugaskan ke ' . $teacher->name . '.';
        if (! empty($skippedNames)) {
            $message .= ' Dilewati (sudah punya Guru Wali): ' . implode(', ', $skippedNames) . '.';
        }

        return back()->with('success', $message);
    }

    public function remove(User $teacher, User $student): RedirectResponse
    {
        StudentHomeroomTeacher::where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->delete();

        return back()->with('success', "{$student->name} berhasil dihapus dari daftar Guru Wali.");
    }
}
