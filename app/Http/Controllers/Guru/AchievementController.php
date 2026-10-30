<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $guru */
        $guru = Auth::user();

        $myExtracurriculars = Extracurricular::with(['pembina', 'teachers'])
            ->where(function ($q) use ($guru) {
                $q->where('pembina_id', $guru->id)
                  ->orWhereHas('teachers', fn ($t) => $t->where('users.id', $guru->id));
            })
            ->orderBy('name')
            ->get();

        $myExtraIds = $myExtracurriculars->pluck('id')->all();

        $baseQuery = StudentAchievement::with([
            'student.schoolClass',
            'category',
            'pembina',
            'extracurricular.teachers',
            'extracurricular.pembina',
        ])->where(function ($q) use ($guru, $myExtraIds) {
            $q->where('pembina_id', $guru->id);
            if (! empty($myExtraIds)) {
                $q->orWhereIn('extracurricular_id', $myExtraIds);
            }
        });

        // Ambil semua data dasar untuk statistik & opsi tahun
        $allBinaanRaw = (clone $baseQuery)->orderByDesc('achievement_date')->orderByDesc('id')->get();
        $allBinaanGrouped = $this->deduplicateTeams($allBinaanRaw);

        $stats = [
            'total'              => $allBinaanGrouped->count(),
            'approved'           => $allBinaanGrouped->where('status', 'approved')->count(),
            'pending'            => $allBinaanGrouped->where('status', 'pending')->count(),
            'nasional_inter'     => $allBinaanGrouped->whereIn('level', ['nasional', 'internasional'])->count(),
            'provinsi_kabupaten' => $allBinaanGrouped->whereIn('level', ['provinsi', 'kabupaten'])->count(),
            'total_students'     => $allBinaanRaw->pluck('student_id')->unique()->count(),
        ];

        $years = $allBinaanRaw
            ->pluck('achievement_date')
            ->filter()
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        // Terapkan filter dari request
        $filteredQuery = clone $baseQuery;

        $extracurricularId = $request->get('extracurricular_id', '');
        $level             = $request->get('level', '');
        $status            = $request->get('status', '');
        $year              = $request->get('year', '');
        $search            = trim((string) $request->get('q', ''));

        if ($extracurricularId !== '') {
            $filteredQuery->where('extracurricular_id', $extracurricularId);
        }

        if ($level !== '') {
            $filteredQuery->where('level', $level);
        }

        if ($status !== '') {
            $filteredQuery->where('status', $status);
        }

        if ($year !== '') {
            $filteredQuery->whereYear('achievement_date', $year);
        }

        if ($search !== '') {
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('event_name', 'like', "%{$search}%")
                  ->orWhere('organizer', 'like', "%{$search}%")
                  ->orWhere('rank', 'like', "%{$search}%")
                  ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%"));
            });
        }

        $filteredRaw  = $filteredQuery->orderByDesc('achievement_date')->orderByDesc('id')->get();
        $achievements = $this->deduplicateTeams($filteredRaw);

        return view('guru.achievement.index', compact(
            'guru',
            'myExtracurriculars',
            'achievements',
            'stats',
            'years',
            'extracurricularId',
            'level',
            'status',
            'year',
            'search'
        ));
    }

    /**
     * Gabungkan prestasi beregu dengan team_code yang sama agar tampil rapi sebagai 1 kartu kejuaraan
     * beserta daftar seluruh anggota timnya.
     */
    protected function deduplicateTeams(\Illuminate\Support\Collection $items): \Illuminate\Support\Collection
    {
        $seenTeams = [];
        $result = collect();

        foreach ($items as $item) {
            if ($item->participation_type === 'beregu' && ! empty($item->team_code)) {
                if (isset($seenTeams[$item->team_code])) {
                    continue;
                }
                $seenTeams[$item->team_code] = true;
            }
            $result->push($item);
        }

        return $result;
    }
}

