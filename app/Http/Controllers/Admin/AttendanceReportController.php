<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminAttendanceSummaryExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;

class AttendanceReportController extends Controller
{
    public function downloadExcel(Request $request)
    {
        $classId     = $request->integer('class_id') ?: null;
        $month       = max(1, min(12, $request->integer('month', now()->month)));
        $year        = $request->integer('year', now()->year);

        [$rows, $workingDays, $monthName] = $this->buildData($classId, $month, $year);

        $showClass = ! $classId;
        $className = $classId ? (SchoolClass::find($classId)?->name ?? 'Kelas') : 'Semua Kelas';
        $filename  = 'laporan_presensi_' . str_replace(' ', '_', $monthName) . '_' . $className . '.xlsx';

        return Excel::download(
            new AdminAttendanceSummaryExport($rows, $monthName, $workingDays, $showClass),
            $filename
        );
    }

    public function downloadPdf(Request $request)
    {
        $classId     = $request->integer('class_id') ?: null;
        $month       = max(1, min(12, $request->integer('month', now()->month)));
        $year        = $request->integer('year', now()->year);

        [$rows, $workingDays, $monthName] = $this->buildData($classId, $month, $year);

        $showClass = ! $classId;
        $className = $classId ? (SchoolClass::find($classId)?->name ?? 'Kelas') : 'Semua Kelas';
        $filename  = 'laporan_presensi_' . str_replace(' ', '_', $monthName) . '_' . $className . '.pdf';

        $avgPct = count($rows) > 0 ? round(collect($rows)->avg('pct'), 1) : 0;

        $html = view('exports.admin-attendance-summary-pdf', [
            'rows'        => $rows,
            'monthName'   => $monthName,
            'workingDays' => $workingDays,
            'showClass'   => $showClass,
            'className'   => $className,
            'avgPct'      => $avgPct,
            'total'       => count($rows),
        ])->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->landscape()
            ->margins(10, 12, 12, 12)
            ->waitUntilNetworkIdle()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function buildData(?int $classId, int $month, int $year): array
    {
        $students = User::with('schoolClass')
            ->where('role', 'siswa')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();

        $workingDays = $this->countWorkingDays($year, $month);
        $studentIds  = $students->pluck('id');

        $allCounts = Attendance::whereIn('user_id', $studentIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('user_id, status, count(*) as total')
            ->groupBy('user_id', 'status')
            ->get()
            ->groupBy('user_id');

        $rows = [];
        foreach ($students as $student) {
            $counts = $allCounts->get($student->id, collect())
                ->pluck('total', 'status')
                ->toArray();

            $hadir      = (int) ($counts['hadir']      ?? 0);
            $terlambat  = (int) ($counts['terlambat']  ?? 0);
            $izin       = (int) ($counts['izin']       ?? 0);
            $sakit      = (int) ($counts['sakit']      ?? 0);
            $alpa       = (int) ($counts['alpa']       ?? 0);
            $dispensasi = (int) ($counts['dispensasi'] ?? 0);

            $present = $hadir + $terlambat + $dispensasi;
            $pct     = $workingDays > 0 ? round($present / $workingDays * 100, 1) : 0;

            $rows[] = [
                'name'       => $student->name,
                'nis'        => $student->nis ?? '—',
                'class'      => $student->schoolClass?->name ?? '—',
                'hadir'      => $hadir,
                'terlambat'  => $terlambat,
                'izin'       => $izin,
                'sakit'      => $sakit,
                'alpa'       => $alpa,
                'dispensasi' => $dispensasi,
                'pct'        => $pct,
            ];
        }

        $monthName = $this->monthName($month) . ' ' . $year;

        return [$rows, $workingDays, $monthName];
    }

    private function countWorkingDays(int $year, int $month): int
    {
        $start = Carbon::createFromDate($year, $month, 1);
        $end   = $start->copy()->endOfMonth();
        $count = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (! $d->isSunday()) $count++;
        }
        return $count;
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',    4 => 'April',
            5 => 'Mei',     6 => 'Juni',     7 => 'Juli',      8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$month] ?? '';
    }
}
