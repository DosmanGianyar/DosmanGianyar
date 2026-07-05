<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeroomConsultation;
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

        return view('admin.guru-wali.show', compact(
            'teacher', 'assignedRecords', 'availableStudents', 'consultations', 'counts'
        ));
    }

    public function assign(Request $request, User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === 'guru', 404);

        $request->validate(['student_id' => 'required|exists:users,id']);

        $student = User::findOrFail($request->student_id);
        abort_unless($student->isSiswa(), 422, 'User bukan siswa.');

        if (StudentHomeroomTeacher::where('student_id', $student->id)->exists()) {
            return back()->with('error', "{$student->name} sudah memiliki Guru Wali.");
        }

        StudentHomeroomTeacher::create([
            'student_id'  => $student->id,
            'teacher_id'  => $teacher->id,
            'assigned_at' => now(),
        ]);

        return back()->with('success', "{$student->name} berhasil ditugaskan ke {$teacher->name}.");
    }

    public function remove(User $teacher, User $student): RedirectResponse
    {
        StudentHomeroomTeacher::where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->delete();

        return back()->with('success', "{$student->name} berhasil dihapus dari daftar Guru Wali.");
    }
}
