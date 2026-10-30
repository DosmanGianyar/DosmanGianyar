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
        $from  = $this->tableFilters['date_range']['from'] ?? ($this->filters['from'] ?? null);
        $until = $this->tableFilters['date_range']['until'] ?? ($this->filters['until'] ?? null);
        $month = $this->tableFilters['month']['value'] ?? ($this->filters['month'] ?? null);
        $year  = $this->tableFilters['year']['value'] ?? ($this->filters['year'] ?? null);

        $approvedQuery = StudentAchievement::query()
            ->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhereIn('curation_status', ['curated', 'not_curatable']);
            })
            ->when($from, fn ($q) => $q->whereDate('achievement_date', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('achievement_date', '<=', $until))
            ->when($month, fn ($q) => $q->whereMonth('achievement_date', $month))
            ->when($year, fn ($q) => $q->whereYear('achievement_date', $year));

        if ($grade !== 'all') {
            $approvedQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
        }

        $allMatching = (clone $approvedQuery)->orderBy('id')->get();
        $groupedCompetitions = $allMatching->groupBy(fn (StudentAchievement $a) => $a->competitionGroupKey());

        $totalSchoolAchievements = $groupedCompetitions->count();
        $nasionalInternasional   = $groupedCompetitions->filter(fn ($group) => in_array($group->first()?->level, ['nasional', 'internasional'], true))->count();
        $uniqueStudents          = $allMatching->pluck('student_id')->unique()->count();

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

