<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\ConductLog;
use App\Models\User;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExecutiveOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'admin_kesiswaan', 'admin_kurikulum', 'admin_sarpras', 'admin_humas'], true);
    }

    protected function getStats(): array
    {
        $grade = $this->filters['grade'] ?? 'all';
        $today = today();

        $siswaQuery = User::whereIn('role', ['siswa', 'pengelola']);
        if ($grade !== 'all') {
            $siswaQuery->whereHas('schoolClass', fn ($q) => $q->where('grade', (string) $grade));
        }
        $totalSiswa = $siswaQuery->count();
        $totalGuru  = User::where('role', 'guru')->count();

        $todayAttQuery = Attendance::where('date', $today);
        if ($grade !== 'all') {
            $todayAttQuery->whereHas('user.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
        }
        $todayAttendances = $todayAttQuery->get();

        $hadirCount     = $todayAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $terlambatCount  = $todayAttendances->where('status', 'terlambat')->count();
        $alpaCount       = $todayAttendances->where('status', 'alpa')->count();

        $pctHadir = $totalSiswa > 0 ? round(($hadirCount / $totalSiswa) * 100, 1) : 0;

        $pelanggaranQuery = ConductLog::where('type', 'pelanggaran')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        if ($grade !== 'all') {
            $pelanggaranQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
        }
        $pelanggaranMonth = $pelanggaranQuery->count();

        $prestasiQuery = ConductLog::where('type', 'prestasi')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        if ($grade !== 'all') {
            $prestasiQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
        }
        $prestasiMonth = $prestasiQuery->count();

        $achieveQuery  = \App\Models\StudentAchievement::where('curation_status', 'pending');
        $permitQuery   = \App\Models\Permit::where('status', 'pending');
        $checkoutQuery = \App\Models\EarlyCheckoutRequest::where('status', 'pending');
        $forgotQuery   = \App\Models\ForgotAttendanceRequest::where('status', 'pending');

        if ($grade !== 'all') {
            $achieveQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
            $permitQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
            $checkoutQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
            $forgotQuery->whereHas('student.schoolClass', fn ($q) => $q->where('grade', (string) $grade));
        }

        $pendingAchievements   = $achieveQuery->count();
        $pendingPermits        = $permitQuery->count();
        $pendingEarlyCheckouts = $checkoutQuery->count();
        $pendingForgotAtts     = $forgotQuery->count();

        $totalPending = $pendingAchievements + $pendingPermits + $pendingEarlyCheckouts + $pendingForgotAtts;
        $gradeLabel   = $grade === 'all' ? '' : " (Kelas {$grade})";

        return [
            Stat::make('Tingkat Kehadiran Hari Ini' . $gradeLabel, $pctHadir . '%')
                ->description("{$hadirCount} Siswa Hadir ({$terlambatCount} Terlambat) • {$alpaCount} Alpa")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($pctHadir >= 95 ? 'success' : ($pctHadir >= 85 ? 'warning' : 'danger')),

            Stat::make('Pengajuan Menunggu Persetujuan' . $gradeLabel, "{$totalPending} Berkas")
                ->description("{$pendingAchievements} Kurasi • {$pendingPermits} Izin • " . ($pendingEarlyCheckouts + $pendingForgotAtts) . " Dispen/Lupa Absen")
                ->descriptionIcon('heroicon-m-clock')
                ->color($totalPending > 0 ? 'warning' : 'success'),

            Stat::make('Total Siswa' . ($grade === 'all' ? ' & Pengelola' : " Kelas {$grade}"), number_format($totalSiswa))
                ->description($grade === 'all' ? "{$totalGuru} Guru & Tenaga Pendidik" : "Siswa Aktif Angkatan Kelas {$grade}")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Poin Kedisiplinan Bulan Ini' . $gradeLabel, "{$pelanggaranMonth} Kasus")
                ->description("{$prestasiMonth} Pencapaian Prestasi Siswa")
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($pelanggaranMonth > 10 ? 'warning' : 'success'),
        ];
    }
}

