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
        $approvedQuery = StudentAchievement::query()->whereIn('curation_status', ['curated', 'not_curatable']);

        $totalApproved     = (clone $approvedQuery)->count();
        $resmiCuratedCount = (clone $approvedQuery)->where('curation_status', 'curated')->count();
        $internalCount     = (clone $approvedQuery)->where('curation_status', 'not_curatable')->count();

        $nasionalInternasional = (clone $approvedQuery)->whereIn('level', ['nasional', 'internasional'])->count();
        $uniqueStudents        = (clone $approvedQuery)->pluck('student_id')->unique()->count();

        return [
            Stat::make('Total Prestasi Diakui', number_format($totalApproved) . ' Capaian')
                ->description("{$resmiCuratedCount} Kurasi Resmi • {$internalCount} Catatan Internal")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Lolos Kurasi Resmi', number_format($resmiCuratedCount) . ' Berkas')
                ->description('Prestasi disahkan standar Puspresnas/SIMT')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),

            Stat::make('Tingkat Nasional & Internasional', number_format($nasionalInternasional))
                ->description('Capaian kejuaraan skala tinggi')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),

            Stat::make('Siswa Berprestasi', number_format($uniqueStudents) . ' Siswa')
                ->description('Total siswa unik penerima kejuaraan')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
