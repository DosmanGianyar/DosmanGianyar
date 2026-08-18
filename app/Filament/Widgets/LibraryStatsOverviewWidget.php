<?php

namespace App\Filament\Widgets;

use App\Models\LibraryLoan;
use App\Models\LibraryVisit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LibraryStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['admin', 'admin_perpustakaan', 'admin_sarpras'], true);
    }

    protected function getStats(): array
    {
        $todayVisits = LibraryVisit::whereDate('visited_at', today())->count();

        $monthVisits = LibraryVisit::whereYear('visited_at', now()->year)
            ->whereMonth('visited_at', now()->month)
            ->count();

        $activeLoans = LibraryLoan::where('status', 'borrowed')->count();

        $allOverdue = LibraryLoan::where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($sub) {
                  $sub->where('status', 'borrowed')
                      ->where('due_at', '<', now()->toDateString());
              });
        })->count();

        return [
            Stat::make('Kunjungan Hari Ini', number_format($todayVisits) . ' Siswa')
                ->description('Siswa baca di tempat hari ini')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('success'),

            Stat::make('Kunjungan Bulan Ini', number_format($monthVisits) . ' Kunjungan')
                ->description(now()->locale('id')->isoFormat('MMMM YYYY'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Peminjaman Aktif', number_format($activeLoans) . ' Buku')
                ->description('Sedang dipinjam siswa')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),

            Stat::make('Peminjaman Terlambat', number_format($allOverdue) . ' Buku')
                ->description($allOverdue > 0 ? 'Perlu tindakan pengembalian' : 'Tidak ada terlambat')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($allOverdue > 0 ? 'danger' : 'success'),
        ];
    }
}
