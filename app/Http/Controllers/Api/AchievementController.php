<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AchievementCategory;
use App\Models\StudentAchievement;
use App\Services\ImageService;
use App\Services\StudentDataService;
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

        return response()->json(StudentDataService::achievements($siswa));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'              => 'required|string|max:200',
            'event_name'         => 'nullable|string|max:200',
            'organizer'          => 'nullable|string|max:200',
            'category_id'        => 'required|exists:achievement_categories,id',
            'field_category'     => 'nullable|string|in:sains_riset,olahraga,seni_budaya,bahasa_debat,keagamaan,akademik,lainnya',
            'level'              => 'required|in:sekolah,kabupaten,provinsi,nasional,internasional',
            'rank'               => 'nullable|string|max:50',
            'participation_type' => 'nullable|in:individu,beregu',
            'achievement_date'   => 'required|date|before_or_equal:today',
            'description'        => 'nullable|string|max:1000',
            'event_url'          => 'nullable|string|max:500',
            'photo'              => 'required|image|max:5120',
            'certificate'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'assignment_letter'  => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $data['student_id']      = $siswa->id;
        $data['status']          = 'pending';
        $data['curation_status'] = 'pending';

        $data['photo'] = ImageService::store(
            $request->file('photo'),
            'achievements/photos/' . $siswa->id,
            1280, 80
        );

        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                $data['certificate'] = ImageService::store($file, 'achievements/certificates/' . $siswa->id, 1600, 85);
            } else {
                $data['certificate'] = $file->store('achievements/certificates/' . $siswa->id, 'public');
            }
        }

        if ($request->hasFile('assignment_letter')) {
            $file = $request->file('assignment_letter');
            if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                $data['assignment_letter'] = ImageService::store($file, 'achievements/letters/' . $siswa->id, 1600, 85);
            } else {
                $data['assignment_letter'] = $file->store('achievements/letters/' . $siswa->id, 'public');
            }
        }

        $achievement = StudentAchievement::create($data);
        $achievement->load('category');

        return response()->json([
            'message'     => 'Laporan prestasi berhasil dikirim dan sedang dalam proses kurasi admin.',
            'achievement' => StudentDataService::formatAchievement($achievement),
        ], 201);
    }
}
