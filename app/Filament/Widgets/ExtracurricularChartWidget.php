<?php

namespace App\Filament\Widgets;

use App\Models\Extracurricular;
use Filament\Widgets\ChartWidget;

class ExtracurricularChartWidget extends ChartWidget
{
    protected ?string $heading = '🏆 Top 5 Ekstrakurikuler Terfavorit';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'admin_kesiswaan', 'admin_kurikulum'], true);
    }

    protected function getData(): array
    {
        $topExtras = Extracurricular::withCount(['activeMembers'])
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
                    'label' => 'Jumlah Anggota Aktif',
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
