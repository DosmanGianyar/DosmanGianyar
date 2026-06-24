<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class AttendanceReportPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Kesiswaan';
    protected static ?string                 $navigationParentItem = 'Presensi';
    protected static ?string                 $navigationLabel      = 'Laporan Presensi';
    protected static ?string                 $title                = 'Laporan Presensi Bulanan';
    protected static ?int                    $navigationSort       = 15;

    protected string $view = 'filament.pages.attendance-report';

    public ?int $classId = null;
    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = (int) now()->month;
        $this->year  = (int) now()->year;
    }

    public function getClasses(): \Illuminate\Support\Collection
    {
        return SchoolClass::orderByRaw("CASE grade WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get(['id', 'name', 'grade']);
    }

    public function getMonthName(): string
    {
        $names = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',    4 => 'April',
            5 => 'Mei',     6 => 'Juni',     7 => 'Juli',      8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($names[$this->month] ?? '') . ' ' . $this->year;
    }

    public function getWorkingDays(): int
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1);
        $end   = $start->copy()->endOfMonth();
        $count = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (! $d->isWeekend()) {
                $count++;
            }
        }
        return $count;
    }

    public function getReportData(): array
    {
        $students = User::with('schoolClass')
            ->where('role', 'siswa')
            ->when($this->classId, fn($q) => $q->where('class_id', $this->classId))
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();

        $workingDays = $this->getWorkingDays();
        $studentIds  = $students->pluck('id');

        $allCounts = Attendance::whereIn('user_id', $studentIds)
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
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

        return [
            'rows'         => $rows,
            'working_days' => $workingDays,
            'total'        => count($rows),
        ];
    }

    public function getYears(): array
    {
        $y = now()->year;
        return range($y - 2, $y + 1);
    }
}
