<?php

namespace App\Filament\Widgets;

use App\Models\StudentAchievement;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AchievementStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $grade = $this->filters['grade'] ?? 'all';
        $from  = $this->tableFilters['date_range']['from'] ?? ($this->filters['from'] ?? now()->startOfYear()->toDateString());
        $until = $this->tableFilters['date_range']['until'] ?? ($this->filters['until'] ?? now()->toDateString());

        $approvedQuery = StudentAchievement::query()
            ->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhereIn('curation_status', ['curated', 'not_curatable']);
            })
            ->when($from, fn ($q) => $q->whereDate('achievement_date', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('achievement_date', '<=', $until));

        if ($grade !== 'all') {
            $approvedQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
        }

        $individualCount = (clone $approvedQuery)->where('participation_type', '!=', 'beregu')->count();
        $teamCount       = (clone $approvedQuery)->where('participation_type', 'beregu')->whereNotNull('team_code')->distinct('team_code')->count('team_code');
        $unkeyedTeam     = (clone $approvedQuery)->where('participation_type', 'beregu')->whereNull('team_code')->distinct('title')->count('title');
        $totalSchoolAchievements = $individualCount + $teamCount + $unkeyedTeam;

        $nasionalInternasional = (clone $approvedQuery)->whereIn('level', ['nasional', 'internasional'])->count();
        $uniqueStudents        = (clone $approvedQuery)->pluck('student_id')->unique()->count();

        $gradeLabel = $grade === 'all' ? '' : " (Kelas {$grade})";

        return [
            Stat::make('Total Prestasi Sekolah' . $gradeLabel, number_format($totalSchoolAchievements) . ' Capaian')
                ->description('Raihan Kejuaraan Sekolah (Lomba Beregu dihitung 1)')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Siswa Berprestasi' . $gradeLabel, number_format($uniqueStudents) . ' Siswa')
                ->description('Total siswa unik penerima apresiasi/kejuaraan')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Tingkat Nasional & Internasional' . $gradeLabel, number_format($nasionalInternasional))
                ->description('Capaian kejuaraan skala tinggi')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),

            Stat::make('Jumlah Total Berkas Siswa' . $gradeLabel, number_format((clone $approvedQuery)->count()) . ' Berkas')
                ->description('Termasuk seluruh berkas anggota tim beregu')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
        ];
    }
}

