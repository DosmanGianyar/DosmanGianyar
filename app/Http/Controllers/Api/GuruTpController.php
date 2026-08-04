<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruTpController extends Controller
{
    // GET /api/v1/guru/tp?subject_id=
    public function index(Request $request): JsonResponse
    {
        $teacher    = Auth::user();
        $mySubjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

        // Tampilkan TP milik sendiri + TP guru lain yg matapalajaran sama
        $query = TujuanPembelajaran::where(function ($q) use ($teacher, $mySubjectIds) {
            $q->where('teacher_id', $teacher->id);   // milik sendiri
            if (count($mySubjectIds)) {
                $q->orWhere(function ($q2) use ($teacher, $mySubjectIds) {
                    $q2->whereIn('subject_id', $mySubjectIds)
                       ->where('teacher_id', '!=', $teacher->id);
                });
            }
        })
        ->with(['subject:id,name', 'teacher:id,name'])
        ->orderByDesc('id');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('grade_level')) {
            $query->where(function ($q) use ($request) {
                $q->where('grade_level', $request->grade_level)
                  ->orWhereNull('grade_level');
            });
        }

        $tps = $query->get();

        return response()->json($tps->map(fn ($tp) => $this->format($tp, $teacher->id)));
    }

    // POST /api/v1/guru/tp
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id'  => 'nullable|exists:subjects,id',
            'grade_level' => 'nullable|string|in:10,11,12',
            'code'        => 'nullable|string|max:30',
            'description' => 'required|string|max:500',
        ]);

        $teacher = Auth::user();

        $tp = TujuanPembelajaran::create([
            'teacher_id'  => $teacher->id,
            'subject_id'  => $request->subject_id,
            'grade_level' => $request->grade_level ?: null,
            'code'        => $request->code,
            'description' => $request->description,
        ]);

        $tp->load(['subject:id,name', 'teacher:id,name']);

        return response()->json([
            'message' => 'TP berhasil disimpan.',
            'tp'      => $this->format($tp, $teacher->id),
        ], 201);
    }

    // PUT /api/v1/guru/tp/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $teacher = Auth::user();
        $tp = TujuanPembelajaran::where('teacher_id', $teacher->id)->findOrFail($id);

        $request->validate([
            'subject_id'  => 'nullable|exists:subjects,id',
            'grade_level' => 'nullable|string|in:10,11,12',
            'code'        => 'nullable|string|max:30',
            'description' => 'required|string|max:500',
        ]);

        $tp->update([
            'subject_id'  => $request->subject_id,
            'grade_level' => $request->grade_level ?: null,
            'code'        => $request->code,
            'description' => $request->description,
        ]);
        $tp->load(['subject:id,name', 'teacher:id,name']);

        return response()->json([
            'message' => 'TP berhasil diperbarui.',
            'tp'      => $this->format($tp, $teacher->id),
        ]);
    }

    // PATCH /api/v1/guru/tp/{id}/toggle
    public function toggle(int $id): JsonResponse
    {
        $teacher = Auth::user();
        $tp = TujuanPembelajaran::where('teacher_id', $teacher->id)->findOrFail($id);
        $tp->update(['is_active' => !$tp->is_active]);
        $tp->load(['subject:id,name', 'teacher:id,name']);

        return response()->json([
            'message'   => $tp->is_active ? 'TP diaktifkan.' : 'TP dinonaktifkan.',
            'is_active' => $tp->is_active,
            'tp'        => $this->format($tp, $teacher->id),
        ]);
    }

    // DELETE /api/v1/guru/tp/{id}
    public function destroy(int $id): JsonResponse
    {
        TujuanPembelajaran::where('teacher_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['message' => 'TP dihapus.']);
    }

    private function format(TujuanPembelajaran $tp, ?int $myTeacherId = null): array
    {
        return [
            'id'           => $tp->id,
            'subject_id'   => $tp->subject_id,
            'subject_name' => $tp->subject?->name,
            'grade_level'  => $tp->grade_level,
            'grade_label'  => $tp->gradeLabel(),
            'code'         => $tp->code,
            'description'  => $tp->description,
            'is_active'    => (bool) $tp->is_active,
            'is_mine'      => $myTeacherId !== null ? ($tp->teacher_id === $myTeacherId) : true,
            'teacher_name' => $tp->teacher?->name,
        ];
    }
}
