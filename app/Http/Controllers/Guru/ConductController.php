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
        'kelas'               => ['label' => 'Pelanggaran Kelas',     'color' => 'yellow', 'type' => 'pelanggaran', 'desc' => 'Pelanggaran yang terjadi saat kegiatan di kelas'],
        'sidak'               => ['label' => 'Pelanggaran Sidak',     'color' => 'red',    'type' => 'pelanggaran', 'desc' => 'Pelanggaran yang ditemukan saat inspeksi mendadak'],
        'lainnya_prestasi'    => ['label' => 'Prestasi Lainnya',      'color' => 'green',  'type' => 'prestasi',    'desc' => 'Catat prestasi dengan deskripsi bebas'],
        'lainnya_pelanggaran' => ['label' => 'Pelanggaran Lainnya',   'color' => 'orange', 'type' => 'pelanggaran', 'desc' => 'Catat pelanggaran dengan deskripsi bebas'],
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
                $student->prestasi_count    = $student->conductLogs->filter(fn ($l) => $l->category?->type === 'prestasi')->count();
                $student->pelanggaran_count = $student->conductLogs->filter(fn ($l) => $l->category?->type === 'pelanggaran')->count();
                return $student;
            });

        return view('guru.conduct.index', compact('classes', 'selectedClassId', 'students'));
    }

    public function choose(): View
    {
        return view('guru.conduct.choose');
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

        ConductLog::create([
            'student_id'  => $request->student_id,
            'teacher_id'  => Auth::id(),
            'category_id' => $category->id,
            'photo'       => $photoPath,
            'note'        => $request->note,
        ]);

        $label = self::CONTEXT_META[$request->context]['label'];
        $desc  = $isLainnya ? $request->note : $category->name;

        NotificationService::send(
            $request->student_id,
            "{$label}: {$desc}",
            "Telah dicatat oleh guru: {$desc}.",
            $category->type === 'prestasi' ? 'success' : 'warning',
            route('siswa.conduct.index'),
        );

        return redirect()->route('guru.conduct.choose')
            ->with('success', "{$label} berhasil dicatat.");
    }

    public function studentDetail(User $student): View
    {
        $logs = $student->conductLogs()
            ->with(['category', 'teacher'])
            ->latest()
            ->paginate(20);

        $prestasiCount    = $student->conductLogs()->whereHas('category', fn ($q) => $q->where('type', 'prestasi'))->count();
        $pelanggaranCount = $student->conductLogs()->whereHas('category', fn ($q) => $q->where('type', 'pelanggaran'))->count();
        $bkLogs           = $student->bkLogs()->with('counselor')->latest()->get();

        return view('guru.conduct.student-detail', compact('student', 'logs', 'prestasiCount', 'pelanggaranCount', 'bkLogs'));
    }
}
