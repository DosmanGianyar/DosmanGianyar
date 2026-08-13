<?php

namespace App\Filament\Widgets;

use App\Models\StudentAchievement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AchievementStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $curatedQuery = StudentAchievement::query()->where('curation_status', 'curated');

        $totalCurated = (clone $curatedQuery)->count();
        $nasionalInternasional = (clone $curatedQuery)->whereIn('level', ['nasional', 'internasional'])->count();
        $uniqueStudents = (clone $curatedQuery)->pluck('student_id')->unique()->filter()->count();

        $topField = (clone $curatedQuery)
            ->selectRaw('field_category, COUNT(*) as count')
            ->groupBy('field_category')
            ->orderByDesc('count')
            ->first();

        $topFieldName = $topField ? (new StudentAchievement(['field_category' => $topField->field_category]))->fieldCategoryLabel() : '—';
        $topFieldCount = $topField ? $topField->count : 0;

        return [
            Stat::make('Total Prestasi Disetujui', number_format($totalCurated))
                ->description('Prestasi terverifikasi & lolos kurasi')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Tingkat Nasional & Internasional', number_format($nasionalInternasional))
                ->description("Capaian kejuaraan skala tinggi")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),

            Stat::make('Siswa Berprestasi', number_format($uniqueStudents) . ' Siswa')
                ->description('Siswa unik peraih kejuaraan')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Rumpun Terbanyak', $topFieldName)
                ->description("{$topFieldCount} Prestasi pada bidang ini")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary'),
        ];
    }
}
