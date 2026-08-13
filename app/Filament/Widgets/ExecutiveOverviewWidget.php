<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\ConductLog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExecutiveOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = today();

        $totalSiswa = User::whereIn('role', ['siswa', 'pengelola'])->count();
        $totalGuru  = User::where('role', 'guru')->count();

        $todayAttendances = Attendance::where('date', $today)->get();
        $hadirCount     = $todayAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $terlambatCount  = $todayAttendances->where('status', 'terlambat')->count();
        $alpaCount       = $todayAttendances->where('status', 'alpa')->count();

        $pctHadir = $totalSiswa > 0 ? round(($hadirCount / $totalSiswa) * 100, 1) : 0;

        $pelanggaranMonth = ConductLog::where('type', 'pelanggaran')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $prestasiMonth = ConductLog::where('type', 'prestasi')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $pendingAchievements   = \App\Models\StudentAchievement::where('curation_status', 'pending')->count();
        $pendingPermits        = \App\Models\Permit::where('status', 'pending')->count();
        $pendingEarlyCheckouts = \App\Models\EarlyCheckoutRequest::where('status', 'pending')->count();
        $pendingForgotAtts     = \App\Models\ForgotAttendanceRequest::where('status', 'pending')->count();

        $totalPending = $pendingAchievements + $pendingPermits + $pendingEarlyCheckouts + $pendingForgotAtts;

        return [
            Stat::make('Tingkat Kehadiran Hari Ini', $pctHadir . '%')
                ->description("{$hadirCount} Siswa Hadir ({$terlambatCount} Terlambat) • {$alpaCount} Alpa")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($pctHadir >= 95 ? 'success' : ($pctHadir >= 85 ? 'warning' : 'danger')),

            Stat::make('Pengajuan Menunggu Persetujuan', "{$totalPending} Berkas")
                ->description("{$pendingAchievements} Kurasi • {$pendingPermits} Izin • " . ($pendingEarlyCheckouts + $pendingForgotAtts) . " Dispen/Lupa Absen")
                ->descriptionIcon('heroicon-m-clock')
                ->color($totalPending > 0 ? 'warning' : 'success'),

            Stat::make('Total Siswa & Pengelola', number_format($totalSiswa))
                ->description("{$totalGuru} Guru & Tenaga Pendidik")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Poin Kedisiplinan Bulan Ini', "{$pelanggaranMonth} Kasus")
                ->description("{$prestasiMonth} Pencapaian Prestasi Siswa")
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($pelanggaranMonth > 10 ? 'warning' : 'success'),
        ];
    }
}
