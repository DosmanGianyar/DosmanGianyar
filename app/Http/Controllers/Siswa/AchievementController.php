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
        $students   = \App\Models\User::whereIn('role', ['siswa', 'pengelola'])
            ->where('id', '!=', Auth::id())
            ->with('schoolClass')
            ->orderBy('name')
            ->get(['id', 'name', 'nisn', 'class_id']);

        return view('siswa.achievement.create', compact('categories', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'is_curation'                 => 'nullable|boolean',
            'title'                       => 'required|string|max:200',
            'event_name'                  => 'nullable|string|max:200',
            'organizer'                   => 'nullable|string|max:200',
            'field_category'              => 'nullable|string|max:50',
            'category_id'                 => 'required|exists:achievement_categories,id',
            'level'                       => 'required|in:sekolah,kabupaten,provinsi,nasional,internasional',
            'rank'                        => 'nullable|string|max:50',
            'participation_type'          => 'nullable|in:individu,beregu',
            'team_member_ids'             => 'nullable|array',
            'team_member_ids.*'           => 'exists:users,id',
            'achievement_date'            => 'required|date|before_or_equal:today',
            'description'                 => 'nullable|string|max:1000',
            'photo'                       => 'required|image|max:5120',
            'certificate'                 => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',

            // Berkas Kurasi (Opsional)
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
        ];

        $data = $request->validate($rules);

        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $teamMemberIds = $data['team_member_ids'] ?? [];
        unset($data['team_member_ids']);

        $data['student_id']     = $siswa->id;
        $data['status']         = 'pending';
        $data['curation_status']= 'pending';
        $data['is_curation']    = false; // Ditentukan oleh Admin/Tim Kurasi Sekolah saat verifikasi

        // Photo kegiatan wajib
        $data['photo'] = ImageService::store(
            $request->file('photo'),
            'achievements/photos/' . $siswa->id,
            1280, 80
        );

        // Certificate
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $data['certificate'] = ImageService::store($file, 'achievements/certificates/' . $siswa->id, 1600, 85);
            } else {
                $data['certificate'] = $file->store('achievements/certificates/' . $siswa->id, 'public');
            }
        }

        // Simpan Berkas Kurasi (jika diunggah oleh siswa)
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
                if (str_starts_with($file->getMimeType(), 'image/')) {
                    $data[$field] = ImageService::store($file, $path, 1600, 85);
                } else {
                    $data[$field] = $file->store($path, 'public');
                }
            }
        }

        StudentAchievement::create($data);

        // Jika beregu dan ada anggota tim yang dipilih, buatkan record otomatis untuk setiap anggota tim
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

        return redirect()->route('siswa.achievements.index')
            ->with('success', 'Pengajuan prestasi & berkas berhasil dikirim! Admin / Kesiswaan akan meninjau dan menentukan status kurasi prestasi Anda.');
    }

    /**
     * Download contoh berkas panduan & sampel kurasi untuk siswa.
     */
    public function downloadExample(string $key)
    {
        $files = [
            'panduan_lengkap' => 'kurasi/Persyaratan Pengisian Kurasi.pdf',
            'poin1'           => 'kurasi/1. Dokumen Standar Penyelenggaraan Cabang Ajang Kompetensi Talenta/Panduan PORSENIJAR.pdf',
            'poin2'           => 'kurasi/2. Tingkatan Seleksi Ajang Kompetensi Talenta/Panduan PORSENIJAR.pdf',
            'poin3'           => 'kurasi/3. Konsistensi Frekuensi Penyelenggaraan Cabang Ajang Kompetensi Talenta/Contoh konsistensi frekuensi penyelenggaran cabang ajang kompetensi talenta.docx',
            'poin4'           => 'kurasi/4. Sarana Prasarana Ajang Kompetensi Talenta/Foto Saat Lomba.png',
            'poin5_piagam'    => 'kurasi/5. Penghargaan dan Apresiasi yang disediakan oleh penyelenggara Ajang Kompetensi Talenta/Piagam juara II Porsenijar 2025 - 48_Ni Made Selsa Sanjiwani.pdf',
            'poin5_rekap'     => 'kurasi/5. Penghargaan dan Apresiasi yang disediakan oleh penyelenggara Ajang Kompetensi Talenta/Rekap Pemenang.pdf',
        ];

        if (!isset($files[$key])) {
            abort(404, 'Contoh berkas tidak ditemukan.');
        }

        $filePath = public_path($files[$key]);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        return response()->download($filePath);
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
