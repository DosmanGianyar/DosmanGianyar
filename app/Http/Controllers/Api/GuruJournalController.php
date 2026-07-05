<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeacherJournal;
use App\Models\TeacherJournalAbsence;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuruJournalController extends Controller
{
    // GET /api/v1/guru/journals?class_id=&month=&year=&page=
    public function index(Request $request): JsonResponse
    {
        $teacher = Auth::user();

        $query = TeacherJournal::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name'])
            ->withCount('absences')
            ->orderByDesc('date');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }

        $journals = $query->paginate(20);

        return response()->json([
            'data' => $journals->map(fn ($j) => $this->formatJournal($j)),
            'meta' => [
                'current_page' => $journals->currentPage(),
                'last_page'    => $journals->lastPage(),
                'total'        => $journals->total(),
            ],
        ]);
    }

    // POST /api/v1/guru/journals
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'             => 'required|exists:classes,id',
            'subject_id'           => 'nullable|exists:subjects,id',
            'date'                 => 'required|date',
            'period'               => 'nullable|integer|min:1|max:12',
            'learning_objectives'  => 'required|string|max:1000',
            'material'             => 'required|string|max:1000',
            'activity'             => 'required|string|max:1000',
            'notes'                => 'nullable|string|max:500',
            'absent_students'      => 'nullable|array',
            'absent_students.*.student_id' => 'required|exists:users,id',
            'absent_students.*.status'     => 'required|in:tidak_hadir,izin,sakit',
        ]);

        $teacher = Auth::user();

        DB::beginTransaction();
        try {
            $journal = TeacherJournal::create([
                'teacher_id'          => $teacher->id,
                'class_id'            => $request->class_id,
                'subject_id'          => $request->subject_id,
                'date'                => $request->date,
                'period'              => $request->period,
                'learning_objectives' => $request->learning_objectives,
                'material'            => $request->material,
                'activity'            => $request->activity,
                'notes'               => $request->notes,
            ]);

            foreach ($request->absent_students ?? [] as $abs) {
                TeacherJournalAbsence::create([
                    'journal_id' => $journal->id,
                    'student_id' => $abs['student_id'],
                    'status'     => $abs['status'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Jurnal berhasil disimpan.',
                'id'      => $journal->id,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    // GET /api/v1/guru/journals/{id}
    public function show(int $id): JsonResponse
    {
        $teacher = Auth::user();
        $journal = TeacherJournal::where('teacher_id', $teacher->id)
            ->with([
                'schoolClass:id,name',
                'subject:id,name',
                'absences.student:id,name,nis',
            ])
            ->findOrFail($id);

        return response()->json($this->formatJournal($journal, withAbsences: true));
    }

    // PUT /api/v1/guru/journals/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $teacher = Auth::user();
        $journal = TeacherJournal::where('teacher_id', $teacher->id)->findOrFail($id);

        $request->validate([
            'learning_objectives'  => 'sometimes|required|string|max:1000',
            'material'             => 'sometimes|required|string|max:1000',
            'activity'             => 'sometimes|required|string|max:1000',
            'notes'                => 'nullable|string|max:500',
            'absent_students'      => 'nullable|array',
            'absent_students.*.student_id' => 'required|exists:users,id',
            'absent_students.*.status'     => 'required|in:tidak_hadir,izin,sakit',
        ]);

        DB::beginTransaction();
        try {
            $journal->update($request->only([
                'learning_objectives', 'material', 'activity', 'notes',
            ]));

            if ($request->has('absent_students')) {
                $journal->absences()->delete();
                foreach ($request->absent_students ?? [] as $abs) {
                    TeacherJournalAbsence::create([
                        'journal_id' => $journal->id,
                        'student_id' => $abs['student_id'],
                        'status'     => $abs['status'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Jurnal berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    // DELETE /api/v1/guru/journals/{id}
    public function destroy(int $id): JsonResponse
    {
        $teacher = Auth::user();
        TeacherJournal::where('teacher_id', $teacher->id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Jurnal dihapus.']);
    }

    // GET /api/v1/guru/journals/class-students/{classId}
    public function classStudents(int $classId): JsonResponse
    {
        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        return response()->json($students);
    }

    private function formatJournal(TeacherJournal $j, bool $withAbsences = false): array
    {
        $data = [
            'id'                   => $j->id,
            'class_id'             => $j->class_id,
            'class_name'           => $j->schoolClass?->name ?? '—',
            'subject_id'           => $j->subject_id,
            'subject_name'         => $j->subject?->name ?? '—',
            'date'                 => $j->date?->format('Y-m-d'),
            'period'               => $j->period,
            'learning_objectives'  => $j->learning_objectives,
            'material'             => $j->material,
            'activity'             => $j->activity,
            'notes'                => $j->notes,
            'absences_count'       => $j->absences_count ?? $j->absences->count(),
        ];

        if ($withAbsences) {
            $data['absent_students'] = $j->absences->map(fn ($a) => [
                'student_id'  => $a->student_id,
                'name'        => $a->student?->name ?? '—',
                'nis'         => $a->student?->nis ?? '—',
                'status'      => $a->status,
            ])->values();
        }

        return $data;
    }
}
