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
        $teacher = Auth::user();

        $query = TujuanPembelajaran::where('teacher_id', $teacher->id)
            ->with('subject:id,name')
            ->orderByDesc('id');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $tps = $query->get();

        return response()->json($tps->map(fn ($tp) => $this->format($tp)));
    }

    // POST /api/v1/guru/tp
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id'  => 'nullable|exists:subjects,id',
            'code'        => 'nullable|string|max:30',
            'description' => 'required|string|max:500',
        ]);

        $tp = TujuanPembelajaran::create([
            'teacher_id'  => Auth::id(),
            'subject_id'  => $request->subject_id,
            'code'        => $request->code,
            'description' => $request->description,
        ]);

        $tp->load('subject:id,name');

        return response()->json([
            'message' => 'TP berhasil disimpan.',
            'tp'      => $this->format($tp),
        ], 201);
    }

    // PUT /api/v1/guru/tp/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $tp = TujuanPembelajaran::where('teacher_id', Auth::id())->findOrFail($id);

        $request->validate([
            'subject_id'  => 'nullable|exists:subjects,id',
            'code'        => 'nullable|string|max:30',
            'description' => 'required|string|max:500',
        ]);

        $tp->update($request->only(['subject_id', 'code', 'description']));
        $tp->load('subject:id,name');

        return response()->json([
            'message' => 'TP berhasil diperbarui.',
            'tp'      => $this->format($tp),
        ]);
    }

    // DELETE /api/v1/guru/tp/{id}
    public function destroy(int $id): JsonResponse
    {
        TujuanPembelajaran::where('teacher_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['message' => 'TP dihapus.']);
    }

    private function format(TujuanPembelajaran $tp): array
    {
        return [
            'id'           => $tp->id,
            'subject_id'   => $tp->subject_id,
            'subject_name' => $tp->subject?->name,
            'code'         => $tp->code,
            'description'  => $tp->description,
        ];
    }
}
