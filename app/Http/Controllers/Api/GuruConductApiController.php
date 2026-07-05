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
            'student_id'   => 'required|exists:users,id',
            'type'         => 'required|in:pelanggaran,prestasi',
            // pelanggaran
            'description'  => 'required_if:type,pelanggaran|nullable|string|max:1000',
            'severity'     => 'required_if:type,pelanggaran|nullable|in:ringan,sedang,berat',
            // prestasi — sub-tipe
            'prestasi_type' => 'required_if:type,prestasi|nullable|in:perilaku,lomba',
            // prestasi perilaku
            'category_id'  => 'required_if:prestasi_type,perilaku|nullable|exists:conduct_categories,id',
            // prestasi lomba
            'lomba_name'   => 'required_if:prestasi_type,lomba|nullable|string|max:200',
            'lomba_level'  => 'required_if:prestasi_type,lomba|nullable|in:sekolah,kabupaten,provinsi,nasional,internasional',
            'lomba_rank'   => 'required_if:prestasi_type,lomba|nullable|in:juara_1,juara_2,juara_3,harapan,peserta',
            'note'         => 'nullable|string|max:500',
        ]);

        $data = [
            'student_id' => $request->student_id,
            'teacher_id' => Auth::id(),
            'note'       => $request->note,
        ];

        // Pelanggaran
        if ($request->type === 'pelanggaran') {
            $data['description'] = $request->description;
            $data['severity']    = $request->severity;

            $severityLabel = match ($request->severity) {
                'ringan' => 'Ringan', 'sedang' => 'Sedang', 'berat' => 'Berat', default => '',
            };

            $log = ConductLog::create($data);
            NotificationService::send($request->student_id, "Pelanggaran ({$severityLabel})", $request->description, 'warning');
            return response()->json(['message' => 'Pelanggaran berhasil dicatat.', 'id' => $log->id], 201);
        }

        // Prestasi Perilaku
        if ($request->prestasi_type === 'perilaku') {
            $category = ConductCategory::findOrFail($request->category_id);
            $data['category_id']   = $category->id;
            $data['prestasi_type'] = 'perilaku';

            $log = ConductLog::create($data);
            NotificationService::send($request->student_id, "Prestasi Perilaku: {$category->name}", "Dicatat oleh guru.", 'success');
            return response()->json(['message' => 'Prestasi perilaku berhasil dicatat.', 'id' => $log->id], 201);
        }

        // Prestasi Lomba
        $rankLabel = match ($request->lomba_rank) {
            'juara_1' => 'Juara 1', 'juara_2' => 'Juara 2', 'juara_3' => 'Juara 3',
            'harapan' => 'Juara Harapan', 'peserta' => 'Peserta', default => '',
        };
        $data['prestasi_type'] = 'lomba';
        $data['lomba_name']    = $request->lomba_name;
        $data['lomba_level']   = $request->lomba_level;
        $data['lomba_rank']    = $request->lomba_rank;

        $log = ConductLog::create($data);
        NotificationService::send($request->student_id, "Prestasi Lomba: {$rankLabel}", $request->lomba_name, 'success');
        return response()->json(['message' => 'Prestasi lomba berhasil dicatat.', 'id' => $log->id], 201);
    }

    // GET /api/v1/guru/conduct-history?type=&page=
    public function history(Request $request): JsonResponse
    {
        $query = ConductLog::with([
                'student:id,name,nis,class_id',
                'student.schoolClass:id,name',
                'category:id,name,type',
            ])
            ->where('teacher_id', Auth::id())
            ->latest();

        if ($request->filled('type')) {
            if ($request->type === 'pelanggaran') {
                $query->where(function ($q) {
                    $q->whereNotNull('severity')
                      ->orWhereHas('category', fn ($c) => $c->where('type', 'pelanggaran'));
                });
            } elseif ($request->type === 'prestasi') {
                $query->whereHas('category', fn ($c) => $c->where('type', 'prestasi'));
            }
        }

        $logs = $query->paginate(20);

        $data = $logs->getCollection()->map(function ($log) {
            // Tentukan tipe: cek category dulu, lalu cek severity
            if ($log->category) {
                $type = $log->category->type;
            } elseif ($log->severity) {
                $type = 'pelanggaran';
            } else {
                $type = 'pelanggaran';
            }

            $lombaLevelLabel = match ($log->lomba_level) {
                'sekolah'       => 'Tingkat Sekolah',
                'kabupaten'     => 'Tingkat Kabupaten/Kota',
                'provinsi'      => 'Tingkat Provinsi',
                'nasional'      => 'Tingkat Nasional',
                'internasional' => 'Tingkat Internasional',
                default         => null,
            };
            $lombaRankLabel = match ($log->lomba_rank) {
                'juara_1' => 'Juara 1',
                'juara_2' => 'Juara 2',
                'juara_3' => 'Juara 3',
                'harapan' => 'Juara Harapan',
                'peserta' => 'Peserta',
                default   => null,
            };

            return [
                'id'               => $log->id,
                'type'             => $type,
                'student_id'       => $log->student_id,
                'student_name'     => $log->student?->name    ?? '—',
                'student_nis'      => $log->student?->nis,
                'class_name'       => $log->student?->schoolClass?->name ?? '—',
                'description'      => $log->description,
                'severity'         => $log->severity,
                'category_name'    => $log->category?->name,
                'prestasi_type'    => $log->prestasi_type,
                'lomba_name'       => $log->lomba_name,
                'lomba_level'      => $log->lomba_level,
                'lomba_level_label' => $lombaLevelLabel,
                'lomba_rank'       => $log->lomba_rank,
                'lomba_rank_label' => $lombaRankLabel,
                'note'             => $log->note,
                'date'             => $log->created_at->format('Y-m-d'),
                'date_label'       => $log->created_at->format('d M Y'),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }
}
