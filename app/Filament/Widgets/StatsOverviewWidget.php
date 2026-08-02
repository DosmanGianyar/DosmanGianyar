<?php

namespace App\Filament\Widgets;

use App\Models\SchoolClass;
use App\Models\Schedule;
use App\Models\StudentAchievement;
use App\Models\Subject;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $siswaCount    = User::where('role', 'siswa')->count();
        $guruCount     = User::where('role', 'guru')->count();
        $kelasCount    = SchoolClass::count();
        $mapelCount    = Subject::count();
        $jadwalCount   = Schedule::count();
        $prestasiCount = StudentAchievement::count();

        return [
            Stat::make('Total Siswa', number_format($siswaCount))
                ->description('Siswa aktif terdaftar')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Total Guru & Pendidik', number_format($guruCount))
                ->description('Tenaga pengajar & pengelola')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Rombongan Belajar (Kelas)', number_format($kelasCount))
                ->description('Total kelas terdaftar')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('warning'),

            Stat::make('Mata Pelajaran', number_format($mapelCount))
                ->description('Kurikulum & Mapel aktif')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),

            Stat::make('Jadwal Pelajaran Master', number_format($jadwalCount))
                ->description('Slot jadwal jam mengajar')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Prestasi Siswa', number_format($prestasiCount))
                ->description('Rekam raihan prestasi')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('gray'),
        ];
    }
}
