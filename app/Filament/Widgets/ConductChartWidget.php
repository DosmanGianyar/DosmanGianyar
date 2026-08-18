<?php

namespace App\Filament\Widgets;

use App\Models\ConductLog;
use Filament\Widgets\ChartWidget;

class ConductChartWidget extends ChartWidget
{
    protected ?string $heading = '📊 Distribusi Pelanggaran Siswa Bulan Ini';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'admin_kesiswaan'], true);
    }

    protected function getData(): array
    {
        $logs = ConductLog::with('category')
            ->where('type', 'pelanggaran')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();

        $grouped = $logs->groupBy(fn ($log) => $log->category?->name ?? 'Lain-lain');

        $labels = [];
        $counts = [];

        foreach ($grouped as $catName => $items) {
            $labels[] = $catName;
            $counts[] = $items->count();
        }

        if (empty($labels)) {
            $labels = ['Belum Ada Pelanggaran 🎉'];
            $counts = [1];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kasus Pelanggaran',
                    'data'  => $counts,
                    'backgroundColor' => [
                        '#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#10b981', '#ec4899', '#6366f1'
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
