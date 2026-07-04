<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AchievementCategory;
use App\Models\StudentAchievement;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa        = Auth::user();
        $achievements = StudentAchievement::where('student_id', $siswa->id)
            ->with('category')
            ->latest()
            ->get();

        $stats = [
            'pending'  => $achievements->where('status', 'pending')->count(),
            'approved' => $achievements->where('status', 'approved')->count(),
            'rejected' => $achievements->where('status', 'rejected')->count(),
        ];

        return view('siswa.achievement.index', compact('achievements', 'stats'));
    }

    public function create(): View
    {
        $categories = AchievementCategory::orderBy('name')->pluck('name', 'id');
        return view('siswa.achievement.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'category_id'      => 'required|exists:achievement_categories,id',
            'level'            => 'required|in:sekolah,kabupaten,provinsi,nasional,internasional',
            'rank'             => 'nullable|string|max:50',
            'achievement_date' => 'required|date|before_or_equal:today',
            'description'      => 'nullable|string|max:1000',
            'photo'            => 'required|image|max:5120',
            'certificate'      => 'nullable|image|max:5120',
        ]);

        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $data['student_id'] = $siswa->id;
        $data['status']     = 'pending';

        $data['photo'] = ImageService::store(
            $request->file('photo'),
            'achievements/photos/' . $siswa->id,
            1280, 80
        );

        if ($request->hasFile('certificate')) {
            $data['certificate'] = ImageService::store(
                $request->file('certificate'),
                'achievements/certificates/' . $siswa->id,
                1600, 85
            );
        }

        StudentAchievement::create($data);

        return redirect()->route('siswa.achievements.index')
            ->with('success', 'Prestasi berhasil dilaporkan dan sedang menunggu verifikasi.');
    }

    public function show(StudentAchievement $achievement): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        // Siswa hanya bisa lihat miliknya sendiri, kecuali pengelola
        if ($achievement->student_id !== $siswa->id && $siswa->role !== 'pengelola') {
            abort(403);
        }

        $achievement->load('student.schoolClass', 'category', 'verifier');
        return view('siswa.achievement.show', compact('achievement'));
    }

    public function report(Request $request): View
    {
        $period     = $request->get('period', 'this_month');
        $level      = $request->get('level', '');
        $categoryId = $request->get('category_id', '');

        $query = StudentAchievement::where('status', 'approved')
            ->with('student.schoolClass', 'category');

        // Period filter
        match ($period) {
            'this_week'  => $query->whereBetween('achievement_date', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('achievement_date', now()->month)->whereYear('achievement_date', now()->year),
            'this_year'  => $query->whereYear('achievement_date', now()->year),
            default      => null,
        };

        if ($level) {
            $query->where('level', $level);
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $achievements = $query->orderByDesc('achievement_date')->get();

        $summary = [
            'sekolah'       => $achievements->where('level', 'sekolah')->count(),
            'kabupaten'     => $achievements->where('level', 'kabupaten')->count(),
            'provinsi'      => $achievements->where('level', 'provinsi')->count(),
            'nasional'      => $achievements->where('level', 'nasional')->count(),
            'internasional' => $achievements->where('level', 'internasional')->count(),
        ];

        $categories = AchievementCategory::orderBy('name')->pluck('name', 'id');

        return view('siswa.achievement.report', compact('achievements', 'summary', 'categories', 'period', 'level', 'categoryId'));
    }
}
