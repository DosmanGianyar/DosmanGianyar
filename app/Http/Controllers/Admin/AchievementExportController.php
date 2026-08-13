<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use App\Models\SchoolClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AchievementExportController extends Controller
{
    /**
     * Unduh Laporan PDF Prestasi Siswa Disetujui
     */
    public function pdf(Request $request)
    {
        $query = StudentAchievement::query()
            ->where('curation_status', 'curated')
            ->with(['student.schoolClass', 'verifier'])
            ->orderBy('achievement_date', 'desc');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('field_category')) {
            $query->where('field_category', $request->field_category);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->class_id));
        }

        if ($request->filled('year')) {
            $query->whereYear('achievement_date', $request->year);
        }

        $achievements = $query->get();

        $selectedClass = $request->filled('class_id') ? SchoolClass::find($request->class_id)?->name : null;
        $selectedLevel = $request->filled('level') ? (new StudentAchievement(['level' => $request->level]))->levelLabel() : null;
        $selectedCategory = $request->filled('field_category') ? (new StudentAchievement(['field_category' => $request->field_category]))->fieldCategoryLabel() : null;

        $stats = [
            'total'         => $achievements->count(),
            'internasional' => $achievements->where('level', 'internasional')->count(),
            'nasional'      => $achievements->where('level', 'nasional')->count(),
            'provinsi'      => $achievements->where('level', 'provinsi')->count(),
            'kabupaten'     => $achievements->where('level', 'kabupaten')->count(),
            'sekolah'       => $achievements->where('level', 'sekolah')->count(),
            'unique_students' => $achievements->pluck('student_id')->unique()->count(),
        ];

        $pdf = Pdf::loadView('exports.achievement-pdf', [
            'achievements'     => $achievements,
            'stats'            => $stats,
            'selectedClass'    => $selectedClass,
            'selectedLevel'    => $selectedLevel,
            'selectedCategory' => $selectedCategory,
            'year'             => $request->year,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan_prestasi_siswa_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Unduh Laporan Excel / CSV Prestasi Siswa Disetujui
     */
    public function excel(Request $request)
    {
        $query = StudentAchievement::query()
            ->where('curation_status', 'curated')
            ->with(['student.schoolClass'])
            ->orderBy('achievement_date', 'desc');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('field_category')) {
            $query->where('field_category', $request->field_category);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->class_id));
        }

        if ($request->filled('year')) {
            $query->whereYear('achievement_date', $request->year);
        }

        $achievements = $query->get();

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
                'No', 'Nama Siswa', 'NISN', 'NIS', 'Kelas', 'No HP Siswa', 'No HP Ortu',
                'Judul Prestasi', 'Event / Lomba', 'Penyelenggara', 'Rumpun Bidang',
                'Tingkat Kejuaraan', 'Peringkat', 'Jenis Partisipasi', 'Tanggal Prestasi',
                'Status Kurasi', 'Verifikator'
            ]);

            foreach ($achievements as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->student?->name ?? '—',
                    $item->student?->nisn ?? '—',
                    $item->student?->nis ?? '—',
                    $item->student?->schoolClass?->name ?? '—',
                    $item->student?->phone ?? '—',
                    $item->student?->parent_phone ?? '—',
                    $item->title,
                    $item->event_name ?? '—',
                    $item->organizer ?? '—',
                    $item->fieldCategoryLabel(),
                    $item->levelLabel(),
                    $item->rank ?? '—',
                    $item->participationTypeLabel(),
                    $item->achievement_date ? $item->achievement_date->format('d/m/Y') : '—',
                    $item->curationStatusLabel(),
                    $item->verifier?->name ?? 'System',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
