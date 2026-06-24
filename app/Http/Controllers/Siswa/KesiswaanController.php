<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EarlyCheckoutRequest;
use App\Models\ExitPass;
use App\Models\ForgotAttendanceRequest;
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
        $absensi = Attendance::where('user_id', $siswa->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $absensiSummary = [
            'hadir'      => (int) ($absensi['hadir']      ?? 0),
            'terlambat'  => (int) ($absensi['terlambat']  ?? 0),
            'izin'       => (int) ($absensi['izin']       ?? 0),
            'sakit'      => (int) ($absensi['sakit']      ?? 0),
            'dispensasi' => (int) ($absensi['dispensasi'] ?? 0),
            'alpa'       => (int) ($absensi['alpa']       ?? 0),
        ];

        // ── Poin & Perilaku ──────────────────────────────────────────────────
        $conductSummary = [
            'total'       => (int) $siswa->conductLogs()->sum('point'),
            'prestasi'    => (int) $siswa->conductLogs()->where('point', '>', 0)->sum('point'),
            'pelanggaran' => (int) abs($siswa->conductLogs()->where('point', '<', 0)->sum('point')),
        ];

        // ── Tab Lists ────────────────────────────────────────────────────────
        $tabPresensi = Attendance::where('user_id', $siswa->id)
            ->latest('date')
            ->limit(15)
            ->get();

        $tabPelanggaran = $siswa->conductLogs()
            ->with('category')
            ->where('point', '<', 0)
            ->latest()
            ->limit(15)
            ->get();

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
        $pendingVerify = $siswa->role === 'siswa_pengelola'
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
            'tabPelanggaran',
            'tabPrestasi',
            'siswa',
            'forgotAttendancePending',
            'earlyCheckoutPending',
        ));
    }
}
