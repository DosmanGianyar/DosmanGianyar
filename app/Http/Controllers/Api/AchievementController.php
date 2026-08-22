<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AchievementCategory;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Services\ImageService;
use App\Services\StudentDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function searchStudents(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));
        $query = User::whereIn('role', ['siswa', 'pengelola'])
            ->where('id', '!=', Auth::id())
            ->with('schoolClass');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%")
                    ->orWhereHas('schoolClass', fn ($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }

        $students = $query->orderBy('name')->limit(30)->get(['id', 'name', 'nisn', 'class_id']);

        return response()->json([
            'students' => $students->map(fn ($s) => [
                'id'         => $s->id,
                'name'       => $s->name,
                'nisn'       => $s->nisn,
                'class_name' => $s->schoolClass?->name ?? '—',
            ]),
        ]);
    }
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
        $rules = [
            'is_curation'        => 'nullable|boolean',
            'title'              => 'required|string|max:200',
            'event_name'         => 'nullable|string|max:200',
            'organizer'          => 'nullable|string|max:200',
            'category_id'        => 'required|exists:achievement_categories,id',
            'field_category'     => 'nullable|string|in:sains_riset,olahraga,seni_budaya,bahasa_debat,keagamaan,akademik,lainnya',
            'level'              => 'required|in:sekolah,kabupaten,provinsi,nasional,internasional',
            'rank'               => 'nullable|string|max:50',
            'participation_type' => 'nullable|in:individu,beregu',
            'team_member_ids'    => 'nullable|array',
            'team_member_ids.*'  => 'exists:users,id',
            'achievement_date'   => 'required|date|before_or_equal:today',
            'description'        => 'nullable|string|max:1000',
            'event_url'          => 'nullable|string|max:500',
            'photo'              => 'required|image|max:5120',
            'certificate'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'assignment_letter'  => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ];

        if ($request->boolean('is_curation')) {
            $rules = array_merge($rules, [
                'doc_standard_checklist'      => 'nullable|array',
                'doc_standard_checklist.*'    => 'string',
                'doc_standard_file'           => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'doc_standard_url'            => 'nullable|url|max:500',

                'selection_level'             => 'nullable|in:3_tingkat,2_tingkat,1_tingkat',
                'selection_level_file'        => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'selection_level_url'         => 'nullable|url|max:500',

                'frequency_consistency'       => 'nullable|in:berturut_gt3,berturut_3,berturut_2,tidak_berturut',
                'frequency_consistency_file'  => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:20480',
                'frequency_consistency_url'   => 'nullable|url|max:500',

                'infrastructure_type'         => 'nullable|in:utama_pendukung,utama,pendukung',
                'infrastructure_file'         => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',

                'reward_types'                => 'nullable|array',
                'reward_types.*'              => 'string',
                'reward_certificate_file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'reward_photo_file'           => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                'reward_recap_file'           => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            ]);
        }

        $data = $request->validate($rules);

        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $teamMemberIds = $data['team_member_ids'] ?? [];
        unset($data['team_member_ids']);

        $data['student_id']      = $siswa->id;
        $data['status']          = 'pending';
        $data['curation_status'] = 'pending';
        $data['is_curation']    = false; // Ditentukan oleh Admin/Tim Kurasi Sekolah saat verifikasi

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

        // Simpan berkas kurasi pendukung jika ada
        $curationFileFields = [
            'doc_standard_file'          => 'curations/doc_standards/' . $siswa->id,
            'selection_level_file'       => 'curations/selection_levels/' . $siswa->id,
            'frequency_consistency_file' => 'curations/frequencies/' . $siswa->id,
            'infrastructure_file'        => 'curations/infrastructures/' . $siswa->id,
            'reward_certificate_file'    => 'curations/rewards/certificates/' . $siswa->id,
            'reward_photo_file'          => 'curations/rewards/photos/' . $siswa->id,
            'reward_recap_file'          => 'curations/rewards/recaps/' . $siswa->id,
        ];

        foreach ($curationFileFields as $field => $path) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                    $data[$field] = ImageService::store($file, $path, 1600, 85);
                } else {
                    $data[$field] = $file->store($path, 'public');
                }
            }
        }

        $achievement = StudentAchievement::create($data);
        $achievement->load('category');

        if (($data['participation_type'] ?? 'individu') === 'beregu' && ! empty($teamMemberIds)) {
            foreach ($teamMemberIds as $memberId) {
                if ((int) $memberId === (int) $siswa->id) {
                    continue;
                }
                $memberData = $data;
                $memberData['student_id'] = $memberId;
                StudentAchievement::create($memberData);
            }
        }

        return response()->json([
            'message'     => 'Laporan prestasi & berkas berhasil dikirim! Admin akan meninjau dan menentukan status kurasi prestasi Anda.',
            'achievement' => StudentDataService::formatAchievement($achievement),
        ], 201);
    }
}
