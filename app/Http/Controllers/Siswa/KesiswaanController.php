<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EarlyCheckoutRequest;
use App\Models\ExitPass;
use App\Models\ForgotAttendanceRequest;
use App\Models\Holiday;
use App\Models\Permit;
use App\Models\StudentAchievement;
use App\Models\VotingSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KesiswaanController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        // ── Rekap Absensi Bulan Ini ──────────────────────────────────────────
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        $monthlyRecs = Attendance::where('user_id', $siswa->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get(['date', 'status']);

        $absensi = $monthlyRecs->groupBy('status')->map->count();

        // Hari sekolah yang lalu tanpa record → dihitung alpa (sama seperti dashboard)
        $monthlyHolidays = Holiday::getHolidayDates($monthStart, $monthEnd, $siswa->class_id);
        $monthlySpecial  = Holiday::getSpecialSchoolDates($monthStart, $monthEnd, $siswa->class_id);
        $recordedDates   = $monthlyRecs->pluck('date')->map->format('Y-m-d')->flip()->all();

        $virtualAlpa = 0;
        $today = today();
        for ($day = $monthStart->copy(); $day->lt($today); $day->addDay()) {
            $ds = $day->format('Y-m-d');
            if (! Holiday::isSchoolDay($day, $monthlyHolidays, $monthlySpecial)) continue;
            if (isset($recordedDates[$ds])) continue;
            $virtualAlpa++;
        }

        $absensiSummary = [
            'hadir'      => (int) ($absensi['hadir']      ?? 0),
            'terlambat'  => (int) ($absensi['terlambat']  ?? 0),
            'izin'       => (int) ($absensi['izin']       ?? 0),
            'sakit'      => (int) ($absensi['sakit']      ?? 0),
            'dispensasi' => (int) ($absensi['dispensasi'] ?? 0),
            'alpa'       => (int) ($absensi['alpa']       ?? 0) + $virtualAlpa,
        ];

        // ── Perilaku (poin dihapus, gunakan count per tipe kategori) ──────────
        $conductSummary = [
            'total'       => (int) $siswa->conductLogs()->count(),
            'prestasi'    => (int) $siswa->conductLogs()->whereHas('category', fn ($q) => $q->where('type', 'prestasi'))->count(),
            'pelanggaran' => (int) $siswa->conductLogs()->whereHas('category', fn ($q) => $q->where('type', 'pelanggaran'))->count(),
        ];

        // ── Tab Lists ────────────────────────────────────────────────────────
        $tabPresensi = Attendance::where('user_id', $siswa->id)
            ->latest('date')
            ->limit(15)
            ->get();

        $tabPelanggaran = $siswa->conductLogs()
            ->with('category')
            ->whereHas('category', fn ($q) => $q->where('type', 'pelanggaran'))
            ->latest()
            ->limit(15)
            ->get();

        // Catatan positif keseharian (BUKAN Prestasi Lomba — itu StudentAchievement
        // yang punya alur verifikasi & sertifikat sendiri, tetap terpisah).
        $tabCatatanPositif = $siswa->conductLogs()
            ->with('category')
            ->whereHas('category', fn ($q) => $q->where('type', 'prestasi'))
            ->latest()
            ->limit(15)
            ->get();

        // Gabungan Catatan Negatif (pelanggaran) + Catatan Positif (keseharian),
        // diurutkan kronologis, untuk tab "Catatan" dengan filter negatif/positif.
        $tabCatatan = $tabPelanggaran->map(fn ($log) => [
                'type'  => 'negatif',
                'date'  => $log->created_at,
                'title' => $log->category?->name ?? $log->note ?? '—',
                'note'  => $log->note,
            ])
            ->concat($tabCatatanPositif->map(fn ($log) => [
                'type'  => 'positif',
                'date'  => $log->created_at,
                'title' => $log->category?->name ?? $log->note ?? '—',
                'note'  => $log->note,
            ]))
            ->sortByDesc('date')
            ->values();

        $tabPrestasi = StudentAchievement::where('student_id', $siswa->id)
            ->with('category')
            ->where('status', 'approved')
            ->latest('achievement_date')
            ->limit(15)
            ->get();

        $recentConduct = $siswa->conductLogs()->with('category')->latest()->limit(3)->get();

        // ── Prestasi ─────────────────────────────────────────────────────────
        $achievementCounts = StudentAchievement::where('student_id', $siswa->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $achievementSummary = [
            'pending'  => (int) ($achievementCounts['pending']  ?? 0),
            'approved' => (int) ($achievementCounts['approved'] ?? 0),
            'rejected' => (int) ($achievementCounts['rejected'] ?? 0),
        ];

        $lastAchievement = StudentAchievement::where('student_id', $siswa->id)
            ->where('status', 'approved')
            ->with('category')
            ->latest('achievement_date')
            ->first();

        // ── Izin & Sakit ──────────────────────────────────────────────────────
        $permitPending = Permit::where('student_id', $siswa->id)
            ->where('status', 'pending')
            ->count();

        $activePermit = Permit::where('student_id', $siswa->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->first();

        // ── Izin Keluar ───────────────────────────────────────────────────────
        $activeExitPass = ExitPass::where('student_id', $siswa->id)
            ->where('status', 'out')
            ->latest()
            ->first();

        // ── E-Voting ──────────────────────────────────────────────────────────
        $activeSessions = VotingSession::where('status', 'active')->get();
        $unvotedCount   = $activeSessions->filter(
            fn ($s) => ! $s->hasVoted($siswa->id)
        )->count();

        // ── Verifikasi Prestasi (pengelola) ───────────────────────────────────
        $pendingVerify = $siswa->role === 'pengelola'
            ? StudentAchievement::where('status', 'pending')->count()
            : 0;

        // ── Lupa Absen ────────────────────────────────────────────────────────
        $forgotAttendancePending = ForgotAttendanceRequest::where('student_id', $siswa->id)
            ->where('status', 'pending')
            ->count();

        // ── Izin Pulang Lebih Awal ────────────────────────────────────────────
        $earlyCheckoutPending = EarlyCheckoutRequest::where('student_id', $siswa->id)
            ->where('status', 'pending')
            ->count();

        return view('siswa.kesiswaan.index', compact(
            'absensiSummary',
            'conductSummary',
            'recentConduct',
            'achievementSummary',
            'lastAchievement',
            'permitPending',
            'activePermit',
            'activeExitPass',
            'activeSessions',
            'unvotedCount',
            'pendingVerify',
            'tabPresensi',
            'tabCatatan',
            'tabPrestasi',
            'siswa',
            'forgotAttendancePending',
            'earlyCheckoutPending',
        ));
    }
}
