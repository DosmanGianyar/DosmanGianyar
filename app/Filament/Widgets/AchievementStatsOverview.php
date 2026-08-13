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

        // 1. Total sertifikat prestasi siswa
        $totalCertificates = (clone $curatedQuery)->count();

        // 2. Total Event Lomba Unik (lomba beregu dari event & judul yang sama dihitung 1 kejuaraan sekolah)
        $uniqueEvents = (clone $curatedQuery)
            ->selectRaw('COUNT(DISTINCT CONCAT(COALESCE(event_name, ""), "|", title, "|", DATE(achievement_date))) as aggregate')
            ->value('aggregate');

        // 3. Breakdown Perorangan vs Beregu
        $bereguCount = (clone $curatedQuery)->where('participation_type', 'beregu')->count();
        $individuCount = $totalCertificates - $bereguCount;

        // 4. Tingkat Nasional & Internasional
        $nasionalInternasional = (clone $curatedQuery)->whereIn('level', ['nasional', 'internasional'])->count();

        return [
            Stat::make('Total Capaian Siswa', number_format($totalCertificates) . ' Sertifikat')
                ->description("{$individuCount} Perorangan • {$bereguCount} Beregu/Kelompok")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Total Event Lomba Unik', number_format($uniqueEvents) . ' Kejuaraan')
                ->description('Jumlah judul event kejuaraan sekolah')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),

            Stat::make('Tingkat Nasional & Internasional', number_format($nasionalInternasional))
                ->description('Capaian skala tinggi (Nasional / Int.)')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),

            Stat::make('Siswa Berprestasi', number_format((clone $curatedQuery)->pluck('student_id')->unique()->count()) . ' Siswa')
                ->description('Total siswa unik penerima kejuaraan')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
