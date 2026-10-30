<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\StudentAchievement;
use App\Models\SchoolClass;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AchievementExportController extends Controller
{
    /**
     * Kelompokkan prestasi sehingga 1 lomba (termasuk lomba beregu / kelompok juara) menjadi 1 baris,
     * lengkap dengan koleksi seluruh siswa juara di dalam kelompok tersebut (`grouped_members`).
     */
    protected function groupByCompetition(Collection $achievements): Collection
    {
        return $achievements
            ->groupBy(fn (StudentAchievement $item) => $item->competitionGroupKey())
            ->map(function (Collection $group): StudentAchievement {
                /** @var StudentAchievement $rep */
                $rep = $group->first();

                // Gabungkan anggota dari grup query dan relasi team_members (jika ada anggota tim di luar filter kelas)
                $members = $group->concat($rep->team_members)
                    ->filter(fn ($m) => $m && $m->student_id)
                    ->sortBy('id')
                    ->unique('student_id')
                    ->values();

                if ($members->isEmpty()) {
                    $members = collect([$rep]);
                }

                $rep->setAttribute('grouped_members', $members);

                // Jika dalam record ada salah satu yang sudah memiliki extracurricular / pembina, gunakan yang terisi
                if (! $rep->extracurricular_id) {
                    $withExtra = $group->first(fn (StudentAchievement $m) => ! empty($m->extracurricular_id));
                    if ($withExtra) {
                        $rep->extracurricular_id = $withExtra->extracurricular_id;
                        $rep->setRelation('extracurricular', $withExtra->extracurricular);
                    }
                }
                if (! $rep->pembina_id) {
                    $withPembina = $group->first(fn (StudentAchievement $m) => ! empty($m->pembina_id));
                    if ($withPembina) {
                        $rep->pembina_id = $withPembina->pembina_id;
                        $rep->setRelation('pembina', $withPembina->pembina);
                    }
                }

                return $rep;
            })
            ->values();
    }

    /**
     * Unduh Laporan PDF Prestasi Siswa Disetujui
     */
    public function pdf(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $query = StudentAchievement::query()
            ->with(['student.schoolClass', 'verifier', 'pembina', 'extracurricular.teachers', 'extracurricular.pembina'])
            ->orderBy('achievement_date', 'desc');

        if ($request->filled('curation_status')) {
            $query->where('curation_status', $request->curation_status);
        } else {
            $query->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhereIn('curation_status', ['curated', 'not_curatable']);
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('field_category')) {
            $query->where('field_category', $request->field_category);
        }

        if ($request->filled('participation_type')) {
            $query->where('participation_type', $request->participation_type);
        }

        if ($request->filled('extracurricular_id')) {
            $query->where('extracurricular_id', $request->extracurricular_id);
        }

        if ($request->filled('pembina_id')) {
            $query->where('pembina_id', $request->pembina_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->class_id));
        }

        if ($request->filled('month')) {
            $query->whereMonth('achievement_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('achievement_date', $request->year);
        }

        if ($request->filled('from')) {
            $query->whereDate('achievement_date', '>=', $request->from);
        }

        if ($request->filled('until')) {
            $query->whereDate('achievement_date', '<=', $request->until);
        }

        $rawAchievements = $query->get();
        $achievements    = $this->groupByCompetition($rawAchievements);

        $selectedClass = $request->filled('class_id') ? SchoolClass::find($request->class_id)?->name : null;
        $selectedLevel = $request->filled('level') ? (new StudentAchievement(['level' => $request->level]))->levelLabel() : null;
        $selectedCategory = $request->filled('field_category') ? (new StudentAchievement(['field_category' => $request->field_category]))->fieldCategoryLabel() : null;
        $selectedCuration = $request->filled('curation_status') ? (new StudentAchievement(['curation_status' => $request->curation_status]))->curationStatusLabel() : null;
        $selectedParticipation = $request->filled('participation_type') ? (new StudentAchievement(['participation_type' => $request->participation_type]))->participationTypeLabel() : null;
        $selectedExtracurricular = $request->filled('extracurricular_id') ? Extracurricular::find($request->extracurricular_id)?->name : null;
        $selectedPembina = $request->filled('pembina_id') ? User::find($request->pembina_id)?->name : null;

        $monthNames = [
            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        $selectedMonth = $request->filled('month') ? ($monthNames[(string) $request->month] ?? null) : null;

        $allStudentIds = $achievements
            ->flatMap(fn (StudentAchievement $item) => ($item->grouped_members ?? collect([$item]))->pluck('student_id'))
            ->filter()
            ->unique();

        $stats = [
            'total'           => $achievements->count(),
            'total_files'     => $rawAchievements->count(),
            'internasional'   => $achievements->where('level', 'internasional')->count(),
            'nasional'        => $achievements->where('level', 'nasional')->count(),
            'provinsi'        => $achievements->where('level', 'provinsi')->count(),
            'kabupaten'       => $achievements->where('level', 'kabupaten')->count(),
            'sekolah'         => $achievements->where('level', 'sekolah')->count(),
            'unique_students' => $allStudentIds->count(),
        ];

        $fromDate = null;
        if ($request->filled('from')) {
            try {
                $fromDate = \Carbon\Carbon::parse($request->from)->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                $fromDate = $request->from;
            }
        }

        $untilDate = null;
        if ($request->filled('until')) {
            try {
                $untilDate = \Carbon\Carbon::parse($request->until)->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                $untilDate = $request->until;
            }
        }

        // Sanitasi karakter emoji 4-byte UTF-8 yang berpotensi menyebabkan crash pada engine font DomPDF
        foreach ($achievements as $item) {
            if ($item->title) {
                $item->title = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $item->title);
            }
            if ($item->event_name) {
                $item->event_name = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $item->event_name);
            }
            if ($item->organizer) {
                $item->organizer = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $item->organizer);
            }
            if ($item->rank) {
                $item->rank = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $item->rank);
            }
        }

        $pdf = Pdf::loadView('exports.achievement-pdf', [
            'achievements'            => $achievements,
            'stats'                   => $stats,
            'selectedClass'           => $selectedClass,
            'selectedLevel'           => $selectedLevel,
            'selectedCategory'        => $selectedCategory,
            'selectedCuration'        => $selectedCuration,
            'selectedParticipation'   => $selectedParticipation,
            'selectedExtracurricular' => $selectedExtracurricular,
            'selectedPembina'         => $selectedPembina,
            'selectedMonth'           => $selectedMonth,
            'year'                    => $request->year,
            'fromDate'                => $fromDate,
            'untilDate'               => $untilDate,
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $filename = 'laporan_prestasi_siswa_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Unduh Laporan Excel / CSV Prestasi Siswa Disetujui
     */
    public function excel(Request $request)
    {
        $query = StudentAchievement::query()
            ->with(['student.schoolClass', 'verifier', 'pembina', 'extracurricular.teachers', 'extracurricular.pembina'])
            ->orderBy('achievement_date', 'desc');

        if ($request->filled('curation_status')) {
            $query->where('curation_status', $request->curation_status);
        } else {
            $query->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhereIn('curation_status', ['curated', 'not_curatable']);
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('field_category')) {
            $query->where('field_category', $request->field_category);
        }

        if ($request->filled('participation_type')) {
            $query->where('participation_type', $request->participation_type);
        }

        if ($request->filled('extracurricular_id')) {
            $query->where('extracurricular_id', $request->extracurricular_id);
        }

        if ($request->filled('pembina_id')) {
            $query->where('pembina_id', $request->pembina_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->class_id));
        }

        if ($request->filled('month')) {
            $query->whereMonth('achievement_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('achievement_date', $request->year);
        }

        if ($request->filled('from')) {
            $query->whereDate('achievement_date', '>=', $request->from);
        }

        if ($request->filled('until')) {
            $query->whereDate('achievement_date', '<=', $request->until);
        }

        $achievements = $this->groupByCompetition($query->get());

        $filename = 'rekap_prestasi_siswa_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($achievements) {
            $file = fopen('php://output', 'w');
            // Add BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'No',
                'Nama Lomba / Judul Prestasi',
                'Nama Siswa / Kelompok Juara',
                'Kelas',
                'NISN',
                'Event / Ajang',
                'Penyelenggara',
                'Rumpun Bidang',
                'Ekstrakurikuler',
                'Tingkat Kejuaraan',
                'Peringkat / Juara',
                'Jenis Partisipasi',
                'Pembina Ekstra',
                'Tanggal Prestasi',
            ]);

            foreach ($achievements as $index => $item) {
                /** @var Collection $members */
                $members = $item->grouped_members ?? collect([$item]);
                $isGroup = $members->count() > 1 || $item->isBeregu();

                if ($isGroup && $members->count() > 1) {
                    $studentNames = $members->values()->map(function ($m, $i) {
                        $cls = $m->student?->schoolClass?->name;
                        return ($i + 1) . '. ' . ($m->student?->name ?? '—') . ($cls ? " ({$cls})" : '');
                    })->implode("\n");
                } else {
                    $studentNames = $item->student?->name ?? '—';
                }

                $classesList = $members->map(fn ($m) => $m->student?->schoolClass?->name)->filter()->unique()->implode(', ') ?: '—';
                $nisnList    = $members->map(fn ($m) => $m->student?->nisn)->filter()->unique()->implode(', ') ?: '—';
                $pembinaList = $item->pembinaList();
                $pembinaText = count($pembinaList) > 1
                    ? collect($pembinaList)->values()->map(fn ($name, $i) => ($i + 1) . '. ' . $name)->implode("\n")
                    : ($pembinaList[0] ?? '—');

                fputcsv($file, [
                    $index + 1,
                    $item->title,
                    $studentNames,
                    $classesList,
                    $nisnList,
                    $item->event_name ?? '—',
                    $item->organizer ?? '—',
                    $item->fieldCategoryLabel(),
                    $item->extracurricularName(),
                    $item->levelLabel(),
                    $item->rank ?? '—',
                    $isGroup ? 'Beregu (' . $members->count() . ' Siswa)' : 'Perorangan',
                    $pembinaText,
                    $item->achievement_date ? \Carbon\Carbon::parse($item->achievement_date)->format('d/m/Y') : '—',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
