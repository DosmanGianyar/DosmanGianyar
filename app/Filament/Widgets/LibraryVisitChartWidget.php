<?php

namespace App\Filament\Widgets;

use App\Models\LibraryVisit;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class LibraryVisitChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = '📚 Grafik Kunjungan Perpustakaan';
    protected static ?int $sort = 3;

    public ?string $filter = '30_days';

    public static function canView(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['admin', 'admin_perpustakaan', 'admin_sarpras'], true);
    }

    protected function getFilters(): ?array
    {
        return [
            '7_days'     => '7 Hari Terakhir',
            '30_days'    => '30 Hari Terakhir',
            'this_month' => 'Bulan Ini',
            'this_year'  => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        $grade        = $this->filters['grade'] ?? 'all';
        $activeFilter = $this->filter ?? '30_days';

        $labels = [];
        $visitData = [];

        $applyGradeFilter = function ($query) use ($grade) {
            if ($grade !== 'all') {
                $query->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
            }
            return $query;
        };

        if ($activeFilter === '7_days') {
            $dates = collect(range(6, 0))->map(fn ($i) => today()->subDays($i));
            foreach ($dates as $date) {
                $labels[] = $date->locale('id')->isoFormat('D MMM');
                $q = LibraryVisit::whereDate('visited_at', $date);
                $visitData[] = $applyGradeFilter($q)->count();
            }
        } elseif ($activeFilter === 'this_month') {
            $start = now()->startOfMonth();
            $end   = now();
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $labels[] = $d->locale('id')->isoFormat('D MMM');
                $q = LibraryVisit::whereDate('visited_at', $d);
                $visitData[] = $applyGradeFilter($q)->count();
            }
        } elseif ($activeFilter === 'this_year') {
            for ($m = 1; $m <= 12; $m++) {
                $date = Carbon::createFromDate(now()->year, $m, 1);
                $labels[] = $date->locale('id')->isoFormat('MMM');
                $q = LibraryVisit::whereYear('visited_at', now()->year)->whereMonth('visited_at', $m);
                $visitData[] = $applyGradeFilter($q)->count();
            }
        } else {
            // Default 30 days
            $dates = collect(range(29, 0))->map(fn ($i) => today()->subDays($i));
            foreach ($dates as $date) {
                $labels[] = $date->locale('id')->isoFormat('D MMM');
                $q = LibraryVisit::whereDate('visited_at', $date);
                $visitData[] = $applyGradeFilter($q)->count();
            }
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Jumlah Kunjungan Siswa' . ($grade !== 'all' ? " (Kelas {$grade})" : ''),
                    'data'            => $visitData,
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

