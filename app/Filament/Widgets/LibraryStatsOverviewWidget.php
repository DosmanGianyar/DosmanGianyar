<?php

namespace App\Filament\Widgets;

use App\Models\LibraryLoan;
use App\Models\LibraryVisit;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LibraryStatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['admin', 'admin_perpustakaan', 'admin_sarpras'], true);
    }

    protected function getStats(): array
    {
        $grade = $this->filters['grade'] ?? 'all';

        $todayVisitsQuery = LibraryVisit::whereDate('visited_at', today());
        $monthVisitsQuery = LibraryVisit::whereYear('visited_at', now()->year)
            ->whereMonth('visited_at', now()->month);
        $activeLoansQuery = LibraryLoan::where('status', 'borrowed');
        $allOverdueQuery  = LibraryLoan::where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($sub) {
                  $sub->where('status', 'borrowed')
                      ->where('due_at', '<', now()->toDateString());
              });
        });

        if ($grade !== 'all') {
            $todayVisitsQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
            $monthVisitsQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
            $activeLoansQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
            $allOverdueQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
        }

        $todayVisits = $todayVisitsQuery->count();
        $monthVisits = $monthVisitsQuery->count();
        $activeLoans = $activeLoansQuery->count();
        $allOverdue  = $allOverdueQuery->count();

        $gradeLabel = $grade === 'all' ? '' : " (Kelas {$grade})";

        return [
            Stat::make('Kunjungan Hari Ini' . $gradeLabel, number_format($todayVisits) . ' Siswa')
                ->description('Siswa baca di tempat hari ini')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('success'),

            Stat::make('Kunjungan Bulan Ini' . $gradeLabel, number_format($monthVisits) . ' Kunjungan')
                ->description(now()->locale('id')->isoFormat('MMMM YYYY'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Peminjaman Aktif' . $gradeLabel, number_format($activeLoans) . ' Buku')
                ->description('Sedang dipinjam siswa')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),

            Stat::make('Peminjaman Terlambat' . $gradeLabel, number_format($allOverdue) . ' Buku')
                ->description($allOverdue > 0 ? 'Perlu tindakan pengembalian' : 'Tidak ada terlambat')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($allOverdue > 0 ? 'danger' : 'success'),
        ];
    }
}

