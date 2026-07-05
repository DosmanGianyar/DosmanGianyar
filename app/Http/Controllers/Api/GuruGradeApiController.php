<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruGradeApiController extends Controller
{
    // GET /api/v1/guru/grades/classes  — kelas yang bisa diakses guru
    public function classes(): JsonResponse
    {
        $teacher = Auth::user();

        $homeroomClass = $teacher->homeroomClass
            ? ['id' => $teacher->homeroomClass->id, 'name' => $teacher->homeroomClass->name]
            : null;

        $teachingClasses = Schedule::where('teacher_id', $teacher->id)
            ->with('schoolClass:id,name')
            ->get()
            ->pluck('schoolClass')
            ->filter()
            ->unique('id')
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->values();

        $all = collect($homeroomClass ? [$homeroomClass] : [])
            ->merge($teachingClasses)
            ->unique('id')
            ->values();

        return response()->json([
            'classes'        => $all,
            'academic_years' => $this->academicYears(),
            'current_year'   => StudentGrade::currentAcademicYear(),
            'current_semester' => StudentGrade::currentSemester(),
        ]);
    }

    // GET /api/v1/guru/grades/subjects
    public function subjects(): JsonResponse
    {
        $subjects = Subject::orderBy('name')->get(['id', 'name']);
        return response()->json($subjects);
    }

    // GET /api/v1/guru/grades?class_id=&semester=&academic_year=
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|in:1,2',
            'academic_year' => 'required|string',
        ]);

        $students = User::where('role', 'siswa')
            ->where('class_id', $request->class_id)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        $grades = StudentGrade::with('subject:id,name')
            ->whereIn('student_id', $students->pluck('id'))
            ->where('semester', $request->semester)
            ->where('academic_year', $request->academic_year)
            ->get()
            ->groupBy('student_id');

        $subjects = Subject::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'students' => $students->map(fn ($s) => [
                'id'   => $s->id,
                'name' => $s->name,
                'nis'  => $s->nis,
                'grades' => collect(['UH', 'UTS', 'UAS'])->flatMap(fn ($type) =>
                    $subjects->map(fn ($sub) => [
                        'grade_id'      => $grades->get($s->id)?->where('subject_id', $sub->id)->where('type', $type)->first()?->id,
                        'subject_id'    => $sub->id,
                        'subject_name'  => $sub->name,
                        'type'          => $type,
                        'score'         => $grades->get($s->id)?->where('subject_id', $sub->id)->where('type', $type)->first()?->score,
                        'notes'         => $grades->get($s->id)?->where('subject_id', $sub->id)->where('type', $type)->first()?->notes,
                    ])
                )->values(),
            ]),
            'subjects' => $subjects,
        ]);
    }

    // POST /api/v1/guru/grades
    public function store(Request $request): JsonResponse
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

        $grade = StudentGrade::updateOrCreate(
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

        return response()->json(['message' => 'Nilai berhasil disimpan.', 'id' => $grade->id], 201);
    }

    // DELETE /api/v1/guru/grades/{id}
    public function destroy(int $id): JsonResponse
    {
        StudentGrade::findOrFail($id)->delete();
        return response()->json(['message' => 'Nilai dihapus.']);
    }

    // GET /api/v1/guru/grades/export?class_id=&semester=&academic_year=
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|in:1,2',
            'academic_year' => 'required|string',
        ]);

        $students = User::where('role', 'siswa')
            ->where('class_id', $request->class_id)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        $subjects = Subject::orderBy('name')->get(['id', 'name']);

        $grades = StudentGrade::whereIn('student_id', $students->pluck('id'))
            ->where('semester', $request->semester)
            ->where('academic_year', $request->academic_year)
            ->get()
            ->groupBy('student_id');

        $class = SchoolClass::find($request->class_id);
        $types = ['UH', 'UTS', 'UAS'];

        $header = ['No', 'NIS', 'Nama'];
        foreach ($subjects as $sub) {
            foreach ($types as $t) {
                $header[] = "{$sub->name} ({$t})";
            }
        }

        $rows = [$header];
        foreach ($students as $i => $s) {
            $row = [$i + 1, $s->nis ?? '—', $s->name];
            foreach ($subjects as $sub) {
                foreach ($types as $t) {
                    $row[] = $grades->get($s->id)?->where('subject_id', $sub->id)->where('type', $t)->first()?->score ?? '';
                }
            }
            $rows[] = $row;
        }

        return response()->json([
            'filename' => "nilai_{$class?->name}_sem{$request->semester}_{$request->academic_year}.csv",
            'rows'     => $rows,
        ]);
    }

    private function academicYears(): array
    {
        $year = now()->year;
        $years = [];
        for ($i = 0; $i <= 2; $i++) {
            $y = $year - $i;
            $years[] = $y . '/' . ($y + 1);
        }
        return $years;
    }
}
