<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\ImageService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConductController extends Controller
{
    public const CONTEXT_META = [
        'akademik'            => ['label' => 'Prestasi Akademik',    'color' => 'green',  'type' => 'prestasi',    'desc' => 'Pencapaian selama kegiatan belajar mengajar'],
        'lomba'               => ['label' => 'Prestasi Lomba',        'color' => 'blue',   'type' => 'prestasi',    'desc' => 'Prestasi siswa dalam perlombaan atau kejuaraan'],
        'kelas'               => ['label' => 'Catatan Negatif Kelas',  'color' => 'yellow', 'type' => 'pelanggaran', 'desc' => 'Catatan negatif yang terjadi saat kegiatan di kelas'],
        'sidak'               => ['label' => 'Catatan Negatif Sidak', 'color' => 'red',    'type' => 'pelanggaran', 'desc' => 'Catatan negatif yang ditemukan saat inspeksi mendadak'],
        'lainnya_prestasi'    => ['label' => 'Prestasi Lainnya',      'color' => 'green',  'type' => 'prestasi',    'desc' => 'Catat prestasi dengan deskripsi bebas'],
        'lainnya_pelanggaran' => ['label' => 'Catatan Negatif Lainnya', 'color' => 'orange', 'type' => 'pelanggaran', 'desc' => 'Catat catatan negatif dengan deskripsi bebas'],
    ];

    public function index(Request $request): View
    {
        $classes = SchoolClass::all();
        $selectedClassId = $request->input('class_id', Auth::user()->homeroomClass?->id ?? $classes->first()?->id);

        $students = User::where('role', 'siswa')
            ->where('class_id', $selectedClassId)
            ->with(['conductLogs.category'])
            ->orderBy('name')
            ->get()
            ->map(function ($student) {
                $student->prestasi_count    = $student->conductLogs->filter(fn ($l) => $l->isPrestasi())->count();
                $student->pelanggaran_count = $student->conductLogs->filter(fn ($l) => $l->isPelanggaran())->count();
                return $student;
            });

        $totalPelanggaran = $students->sum('pelanggaran_count');
        $totalPrestasi    = $students->sum('prestasi_count');

        return view('guru.conduct.index', compact('classes', 'selectedClassId', 'students', 'totalPelanggaran', 'totalPrestasi'));
    }

    public function choose(): View
    {
        $prestasiCategories = ConductCategory::active()
            ->where('type', 'prestasi')
            ->where('name', 'not like', '__sistem__%')
            ->orderBy('context')
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::with(['students' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();

        $recentLogs = ConductLog::where('teacher_id', Auth::id())
            ->with(['student.schoolClass', 'category'])
            ->latest()
            ->limit(20)
            ->get();

        return view('guru.conduct.choose', compact('prestasiCategories', 'classes', 'recentLogs'));
    }

    public function create(Request $request): View
    {
        $context = $request->input('context');
        abort_unless(array_key_exists($context, self::CONTEXT_META), 404);

        $contextMeta = self::CONTEXT_META[$context];
        $isLainnya   = str_starts_with($context, 'lainnya_');

        $categories = $isLainnya
            ? collect()
            : ConductCategory::active()->context($context)->orderBy('name')->get();

        $classes              = SchoolClass::with('students')->get();
        $preselectedStudentId = $request->input('student_id');

        return view('guru.conduct.create', compact(
            'categories', 'classes', 'preselectedStudentId', 'context', 'contextMeta', 'isLainnya'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $isLainnya = str_starts_with($request->input('context', ''), 'lainnya_');

        $rules = [
            'student_id' => 'required|exists:users,id',
            'context'    => 'required|in:' . implode(',', array_keys(self::CONTEXT_META)),
            'note'       => ($isLainnya ? 'required' : 'nullable') . '|string|max:500',
            'photo'      => 'nullable|image|max:2048',
        ];

        if (! $isLainnya) {
            $rules['category_id'] = 'required|exists:conduct_categories,id';
        }

        $request->validate($rules);

        if ($isLainnya) {
            $type     = self::CONTEXT_META[$request->context]['type'];
            $catName  = $type === 'prestasi' ? '__sistem__prestasi_lainnya' : '__sistem__pelanggaran_lainnya';
            $category = ConductCategory::firstOrCreate(
                ['name' => $catName],
                ['type' => $type, 'context' => $request->context, 'is_active' => true]
            );
        } else {
            $category = ConductCategory::findOrFail($request->category_id);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = ImageService::store($request->file('photo'), 'conduct', maxWidth: 1280, quality: 80);
        }

        $type = self::CONTEXT_META[$request->context]['type'];

        $log = ConductLog::create([
            'student_id'  => $request->student_id,
            'teacher_id'  => Auth::id(),
            'category_id' => $category->id,
            'photo'       => $photoPath,
            'note'        => $request->note,
            'type'        => $type,
        ]);

        $label = self::CONTEXT_META[$request->context]['label'];
        $desc  = $isLainnya ? $request->note : $category->name;

        $student = User::find($request->student_id);
        if ($student) {
            NotificationService::send(
                $student->id,
                "{$label}: {$desc}",
                "Telah dicatat oleh guru: {$desc}.",
                $type === 'prestasi' ? 'success' : 'warning',
                route('siswa.conduct.index'),
            );
            NotificationService::notifyParentsOfStudent(
                $student,
                "Catatan Perilaku Anak",
                "Ananda {$student->name}: {$label} - {$desc}",
                $type === 'prestasi' ? 'success' : 'warning',
            );
            NotificationService::notifyHomeroomTeacher(
                $student,
                "Catatan Perilaku Siswa Kelas",
                "Siswa {$student->name}: {$label} - {$desc}",
                $type === 'prestasi' ? 'success' : 'warning',
            );
        }

        return redirect()->route('guru.conduct.choose')
            ->with('success', "{$label} berhasil dicatat.");
    }

    public function studentDetail(User $student): View
    {
        $logs = $student->conductLogs()
            ->with(['category', 'teacher'])
            ->latest()
            ->paginate(20);

        $allLogs          = $student->conductLogs()->with('category')->get();
        $prestasiCount    = $allLogs->filter(fn ($l) => $l->isPrestasi())->count();
        $pelanggaranCount = $allLogs->filter(fn ($l) => $l->isPelanggaran())->count();
        $bkLogs           = $student->bkLogs()->with('counselor')->latest()->get();

        return view('guru.conduct.student-detail', compact('student', 'logs', 'prestasiCount', 'pelanggaranCount', 'bkLogs'));
    }

    public function scanLookup(Request $request): \Illuminate\Http\JsonResponse
    {
        $code = trim((string) $request->input('code', ''));
        if (! $code) {
            return response()->json(['success' => false, 'message' => 'Kode scan barcode kosong.'], 400);
        }

        if (str_contains($code, '/')) {
            $parts = explode('/', rtrim($code, '/'));
            $code = end($parts);
        }

        $student = User::where('role', 'like', 'siswa%')
            ->where(function ($q) use ($code) {
                $q->where('qr_code_token', $code)
                  ->orWhere('nisn', $code)
                  ->orWhere('nis', $code);
                if (is_numeric($code)) {
                    $q->orWhere('id', (int) $code);
                }
            })
            ->with('schoolClass:id,name')
            ->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => "Siswa dengan NISN/NIS/ID '{$code}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'student' => [
                'id'           => $student->id,
                'name'         => $student->name,
                'nis'          => $student->nis,
                'nisn'         => $student->nisn,
                'class_id'     => $student->class_id,
                'class_name'   => $student->schoolClass?->name ?? '—',
                'parent_name'  => $student->parent_name,
                'parent_phone' => $student->parent_phone,
            ],
        ]);
    }

    public function verificationIndex(Request $request): View
    {
        $today = now()->format('Y-m-d');

        $pendingLogs = ConductLog::where('is_self_reported', true)
            ->where('status', 'pending')
            ->whereDate('created_at', $today)
            ->with(['student.schoolClass', 'student.conductLogs', 'category'])
            ->latest()
            ->get()
            ->map(function ($log) {
                if ($log->student) {
                    $log->student->lateness_count = $log->student->conductLogs
                        ->filter(fn ($l) => $l->isPelanggaran())
                        ->count();
                }
                return $log;
            });

        $verifiedLogs = ConductLog::where('is_self_reported', true)
            ->where('status', 'verified')
            ->whereDate('created_at', $today)
            ->with(['student.schoolClass', 'student.conductLogs', 'category', 'verifier'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($log) {
                if ($log->student) {
                    $log->student->lateness_count = $log->student->conductLogs
                        ->filter(fn ($l) => $l->isPelanggaran())
                        ->count();
                }
                return $log;
            });

        return view('guru.conduct.verification', compact('pendingLogs', 'verifiedLogs'));
    }

    public function verifyLog(ConductLog $log): RedirectResponse
    {
        if ($log->status === 'verified') {
            return redirect()->back()->with('info', 'Pengajuan ini sudah diverifikasi sebelumnya.');
        }

        $log->update([
            'status'      => 'verified',
            'verified_at' => now(),
            'verifier_id' => Auth::id(),
            'teacher_id'  => Auth::id(),
        ]);

        $student = $log->student;
        if ($student) {
            NotificationService::send(
                $student->id,
                "Pembinaan Disiplin Diverifikasi",
                "Pengajuan pembinaan Anda telah diverifikasi oleh " . Auth::user()->name . ". Selamat belajar!",
                'success',
                route('siswa.conduct.index')
            );
            NotificationService::notifyParentsOfStudent(
                $student,
                "Catatan Kedisiplinan Siswa",
                "Ananda {$student->name} telah melakukan pengajuan pembinaan keterlambatan dan diverifikasi oleh guru.",
                'warning'
            );
        }

        return redirect()->back()->with('success', "Pengajuan pembinaan {$student?->name} berhasil diverifikasi. Siswa diizinkan masuk.");
    }
}
