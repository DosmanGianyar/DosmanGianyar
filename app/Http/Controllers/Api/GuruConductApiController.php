<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruConductApiController extends Controller
{
    // GET /api/v1/guru/conduct-categories
    public function categories(): JsonResponse
    {
        $categories = ConductCategory::active()
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'context']);

        return response()->json([
            'prestasi'    => $categories->where('type', 'prestasi')->values(),
            'pelanggaran' => $categories->where('type', 'pelanggaran')->values(),
        ]);
    }

    // GET /api/v1/guru/conduct-students?class_id=&q=
    public function students(Request $request): JsonResponse
    {
        $query = User::where('role', 'siswa')->with('schoolClass:id,name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $students = $query->orderBy('name')->limit(50)->get(['id', 'name', 'nis', 'class_id']);

        return response()->json($students->map(fn ($s) => [
            'id'         => $s->id,
            'name'       => $s->name,
            'nis'        => $s->nis,
            'class_name' => $s->schoolClass?->name ?? '—',
        ]));
    }

    // GET /api/v1/guru/conduct-classes
    public function classes(): JsonResponse
    {
        $classes = SchoolClass::orderBy('name')->get(['id', 'name']);
        return response()->json($classes);
    }

    // POST /api/v1/guru/conduct-logs
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id'  => 'required|exists:users,id',
            'type'        => 'required|in:pelanggaran,prestasi',
            // pelanggaran: wajib description + severity
            'description' => 'required_if:type,pelanggaran|nullable|string|max:1000',
            'severity'    => 'required_if:type,pelanggaran|nullable|in:ringan,sedang,berat',
            // prestasi: wajib category_id
            'category_id' => 'required_if:type,prestasi|nullable|exists:conduct_categories,id',
            'note'        => 'nullable|string|max:500',
        ]);

        $data = [
            'student_id' => $request->student_id,
            'teacher_id' => Auth::id(),
            'note'       => $request->note,
        ];

        if ($request->type === 'pelanggaran') {
            $data['description'] = $request->description;
            $data['severity']    = $request->severity;

            $severityLabel = match ($request->severity) {
                'ringan' => 'Ringan',
                'sedang' => 'Sedang',
                'berat'  => 'Berat',
                default  => '',
            };

            $log = ConductLog::create($data);

            NotificationService::send(
                $request->student_id,
                "Pelanggaran ({$severityLabel})",
                $request->description,
                'warning',
            );

            return response()->json(['message' => 'Pelanggaran berhasil dicatat.', 'id' => $log->id], 201);
        }

        // Prestasi — pakai category_id
        $category = ConductCategory::findOrFail($request->category_id);
        $data['category_id'] = $category->id;

        $log = ConductLog::create($data);

        NotificationService::send(
            $request->student_id,
            "Prestasi: {$category->name}",
            "Telah dicatat oleh guru.",
            'success',
        );

        return response()->json(['message' => 'Prestasi berhasil dicatat.', 'id' => $log->id], 201);
    }
}
