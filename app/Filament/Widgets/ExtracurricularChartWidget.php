<?php

namespace App\Filament\Widgets;

use App\Models\Extracurricular;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ExtracurricularChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = '🏆 Top 5 Ekstrakurikuler Terfavorit';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'admin_kesiswaan', 'admin_kurikulum'], true);
    }

    protected function getData(): array
    {
        $grade = $this->filters['grade'] ?? 'all';

        $topExtrasQuery = Extracurricular::withCount(['activeMembers' => function ($q) use ($grade) {
            if ($grade !== 'all') {
                $q->whereHas('student.schoolClass', fn ($classQ) => $classQ->where('grade', (string) $grade));
            }
        }]);

        $topExtras = $topExtrasQuery
            ->orderByDesc('active_members_count')
            ->take(5)
            ->get();

        $labels = $topExtras->pluck('name')->toArray();
        $counts = $topExtras->pluck('active_members_count')->toArray();

        if (empty($labels)) {
            $labels = ['Belum Ada Ekstrakurikuler'];
            $counts = [0];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Anggota Aktif' . ($grade !== 'all' ? " (Kelas {$grade})" : ''),
                    'data'  => $counts,
                    'backgroundColor' => '#f59e0b',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

