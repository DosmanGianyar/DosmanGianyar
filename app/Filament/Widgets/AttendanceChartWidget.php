<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class AttendanceChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = '📈 Tren Kehadiran Siswa (7 Hari Terakhir)';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'admin_kesiswaan', 'admin_kurikulum'], true);
    }

    protected function getData(): array
    {
        $grade = $this->filters['grade'] ?? 'all';
        $dates = collect(range(6, 0))->map(fn ($i) => today()->subDays($i));

        $hadirData     = [];
        $terlambatData = [];
        $lupaAbsenData = [];
        $alpaData      = [];
        $labels        = [];

        foreach ($dates as $date) {
            $labels[] = $date->locale('id')->isoFormat('D MMM');

            $dayAttQuery = Attendance::where('date', $date);
            if ($grade !== 'all') {
                $dayAttQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
            }

            $dayAtt = $dayAttQuery->get();

            $hadirData[]     = $dayAtt->where('status', 'hadir')->where('via_lupa_absen', false)->count();
            $terlambatData[] = $dayAtt->where('status', 'terlambat')->where('via_lupa_absen', false)->count();
            $lupaAbsenData[] = $dayAtt->filter(fn ($a) => (bool)$a->via_lupa_absen || $a->status === 'lupa_absen')->count();
            $alpaData[]      = $dayAtt->where('status', 'alpa')->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Hadir Tepat Waktu',
                    'data'            => $hadirData,
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill'            => true,
                ],
                [
                    'label'           => 'Terlambat',
                    'data'            => $terlambatData,
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill'            => true,
                ],
                [
                    'label'           => 'Lupa Absen',
                    'data'            => $lupaAbsenData,
                    'borderColor'     => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'fill'            => true,
                ],
                [
                    'label'           => 'Alpa',
                    'data'            => $alpaData,
                    'borderColor'     => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.15)',
                    'fill'            => true,
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




