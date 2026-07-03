<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AchievementCategory;
use App\Models\StudentAchievement;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function categories(): JsonResponse
    {
        $cats = AchievementCategory::orderBy('name')->get(['id', 'name']);
        return response()->json(['categories' => $cats]);
    }

    public function index(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $achievements = StudentAchievement::with('category')
            ->where('student_id', $siswa->id)
            ->latest()
            ->get();

        $stats = [
            'pending'  => $achievements->where('status', 'pending')->count(),
            'approved' => $achievements->where('status', 'approved')->count(),
            'rejected' => $achievements->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'stats'        => $stats,
            'achievements' => $achievements->map(fn ($a) => $this->format($a))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
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

        $achievement = StudentAchievement::create($data);
        $achievement->load('category');

        return response()->json([
            'message'     => 'Prestasi berhasil dilaporkan dan sedang menunggu verifikasi.',
            'achievement' => $this->format($achievement),
        ], 201);
    }

    private function format(StudentAchievement $a): array
    {
        return [
            'id'               => $a->id,
            'title'            => $a->title,
            'category_name'    => $a->category?->name,
            'level'            => $a->level,
            'level_label'      => $a->levelLabel(),
            'rank'             => $a->rank,
            'achievement_date' => $a->achievement_date->toDateString(),
            'description'      => $a->description,
            'status'           => $a->status,
            'status_label'     => $a->statusLabel(),
            'rejection_reason' => $a->rejection_reason,
            'photo_url'        => $a->photoUrl(),
            'certificate_url'  => $a->certificateUrl(),
            'created_at'       => $a->created_at->toIso8601String(),
        ];
    }
}
