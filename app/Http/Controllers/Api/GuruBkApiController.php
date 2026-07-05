<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BkLog;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruBkApiController extends Controller
{
    // GET /api/v1/guru/bk/students?class_id=&q=
    public function students(Request $request): JsonResponse
    {
        $teacher = Auth::user();

        $query = User::where('role', 'siswa')->orderBy('name');

        // filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        } else {
            // only students in teacher's accessible classes
            $classIds = $this->accessibleClassIds($teacher);
            if ($classIds->isNotEmpty()) {
                $query->whereIn('class_id', $classIds);
            }
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($b) => $b->where('name', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%"));
        }

        return response()->json(
            $query->limit(50)->get(['id', 'name', 'nis', 'class_id'])
                ->map(fn ($s) => [
                    'id'        => $s->id,
                    'name'      => $s->name,
                    'nis'       => $s->nis,
                    'class_name'=> $s->schoolClass?->name,
                ])
        );
    }

    // GET /api/v1/guru/bk?class_id=&student_id=&page=
    public function index(Request $request): JsonResponse
    {
        $teacher = Auth::user();

        $query = BkLog::with(['student:id,name,nis,class_id', 'counselor:id,name'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        } elseif ($request->filled('class_id')) {
            $studentIds = User::where('role', 'siswa')
                ->where('class_id', $request->class_id)
                ->pluck('id');
            $query->whereIn('student_id', $studentIds);
        } else {
            $classIds   = $this->accessibleClassIds($teacher);
            $studentIds = User::where('role', 'siswa')->whereIn('class_id', $classIds)->pluck('id');
            $query->whereIn('student_id', $studentIds);
        }

        $paginated = $query->paginate(20);

        return response()->json([
            'data' => collect($paginated->items())->map(fn ($log) => [
                'id'            => $log->id,
                'student_id'    => $log->student_id,
                'student_name'  => $log->student?->name,
                'student_nis'   => $log->student?->nis,
                'class_name'    => $log->student?->schoolClass?->name,
                'coaching_note' => $log->coaching_note,
                'date'          => $log->date?->format('Y-m-d'),
                'counselor_name'=> $log->counselor?->name,
                'is_auto'       => $log->is_auto,
            ]),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    // POST /api/v1/guru/bk
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id'    => 'required|exists:users,id',
            'coaching_note' => 'required|string|max:1000',
            'date'          => 'required|date|before_or_equal:today',
        ]);

        BkLog::create([
            'student_id'    => $request->student_id,
            'counselor_id'  => Auth::id(),
            'coaching_note' => $request->coaching_note,
            'date'          => $request->date,
            'is_auto'       => false,
        ]);

        return response()->json(['message' => 'Catatan BK berhasil disimpan.'], 201);
    }

    // GET /api/v1/guru/bk/classes
    public function classes(): JsonResponse
    {
        $teacher  = Auth::user();
        $classIds = $this->accessibleClassIds($teacher);

        $classes = \App\Models\SchoolClass::whereIn('id', $classIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($classes);
    }

    private function accessibleClassIds(User $teacher)
    {
        $ids = Schedule::where('teacher_id', $teacher->id)->pluck('class_id');

        if ($teacher->homeroomClass) {
            $ids->push($teacher->homeroomClass->id);
        }

        return $ids->unique()->values();
    }
}
