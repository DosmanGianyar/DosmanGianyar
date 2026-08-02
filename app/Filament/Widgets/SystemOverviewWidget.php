<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\DamageReport;
use App\Models\Permit;
use App\Models\TeacherAttendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $today = now()->format('Y-m-d');

        $siswaHadir  = Attendance::whereDate('date', $today)->where('status', 'hadir')->count();
        $guruHadir   = TeacherAttendance::whereDate('date', $today)->where('status', 'hadir')->count();
        $permitCount = Permit::whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count();
        $sarprasPending = DamageReport::where('status', 'pending')->count();

        return [
            Stat::make('Presensi Siswa Hari Ini', number_format($siswaHadir) . ' Siswa')
                ->description('Hadir di sekolah hari ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Presensi Guru Hari Ini', number_format($guruHadir) . ' Guru')
                ->description('Hadir / Check-in hari ini')
                ->descriptionIcon('heroicon-m-user-check')
                ->color('info'),

            Stat::make('Izin / Dispensasi Aktif', number_format($permitCount) . ' Permohonan')
                ->description('Siswa/Guru izin hari ini')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Laporan Sarpras Pending', number_format($sarprasPending) . ' Laporan')
                ->description('Perlu ditindaklanjuti')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($sarprasPending > 0 ? 'danger' : 'success'),
        ];
    }
}
