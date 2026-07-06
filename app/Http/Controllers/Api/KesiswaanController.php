<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConductLog;
use App\Models\EarlyCheckoutRequest;
use App\Models\ForgotAttendanceRequest;
use App\Models\Permit;
use App\Models\StudentAchievement;
use App\Models\VotingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class KesiswaanController extends Controller
{
    public function summary(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        // ── Izin / Sakit / Dispensasi ─────────────────────────────────────
        $permitPending = Permit::where('student_id', $siswa->id)
            ->where('status', 'pending')
            ->count();

        $activePermit = Permit::where('student_id', $siswa->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->first();

        // ── Izin Pulang Lebih Awal ────────────────────────────────────────
        $earlyCheckoutPending = EarlyCheckoutRequest::where('student_id', $siswa->id)
            ->where('status', 'pending')
            ->count();

        // ── Lupa Absen ────────────────────────────────────────────────────
        $forgotAttendancePending = ForgotAttendanceRequest::where('student_id', $siswa->id)
            ->where('status', 'pending')
            ->count();

        // ── Prestasi ─────────────────────────────────────────────────────
        $achievementStats = StudentAchievement::where('student_id', $siswa->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingVerify = $siswa->role === 'pengelola'
            ? StudentAchievement::where('status', 'pending')->count()
            : 0;

        $recentAchievements = StudentAchievement::with('category')
            ->where('student_id', $siswa->id)
            ->where('status', 'approved')
            ->latest('achievement_date')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id'               => $a->id,
                'title'            => $a->title,
                'category_name'    => $a->category?->name,
                'level'            => $a->level,
                'level_label'      => $a->levelLabel(),
                'achievement_date' => $a->achievement_date->toDateString(),
            ])->values();

        // ── E-Voting ──────────────────────────────────────────────────────
        $activeSessions = VotingSession::where('status', 'active')->get();
        $unvotedCount   = $activeSessions->filter(
            fn ($s) => ! $s->hasVoted($siswa->id)
        )->count();

        // ── Conduct (pelanggaran recent) ──────────────────────────────────
        $recentViolations = ConductLog::with('category')
            ->where('student_id', $siswa->id)
            ->whereHas('category', fn ($q) => $q->where('type', 'pelanggaran'))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($log) => [
                'id'            => $log->id,
                'category_name' => $log->category->name,
                'context'       => $log->category->context ?? null,
                'note'          => $log->note,
                'date'          => $log->created_at->toDateString(),
            ])->values();

        $pelanggaranCount = ConductLog::where('student_id', $siswa->id)
            ->whereHas('category', fn ($q) => $q->where('type', 'pelanggaran'))
            ->count();

        // Catatan positif keseharian (BUKAN Prestasi Lomba — StudentAchievement
        // punya alur verifikasi & sertifikat sendiri, tetap terpisah).
        $recentPositif = ConductLog::with('category')
            ->where('student_id', $siswa->id)
            ->whereHas('category', fn ($q) => $q->where('type', 'prestasi'))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($log) => [
                'id'            => $log->id,
                'category_name' => $log->category->name,
                'context'       => $log->category->context ?? null,
                'note'          => $log->note,
                'date'          => $log->created_at->toDateString(),
            ])->values();

        return response()->json([
            'permit_pending'            => $permitPending,
            'early_checkout_pending'    => $earlyCheckoutPending,
            'forgot_attendance_pending' => $forgotAttendancePending,
            'unvoted_count'             => $unvotedCount,
            'pending_verify'            => $pendingVerify,
            'achievement_stats' => [
                'pending'  => (int) ($achievementStats['pending']  ?? 0),
                'approved' => (int) ($achievementStats['approved'] ?? 0),
                'rejected' => (int) ($achievementStats['rejected'] ?? 0),
            ],
            'active_permit' => $activePermit ? [
                'type'       => $activePermit->type,
                'start_date' => $activePermit->start_date->toDateString(),
                'end_date'   => $activePermit->end_date->toDateString(),
            ] : null,
            'conduct' => [
                'pelanggaran_count' => $pelanggaranCount,
                'recent_violations' => $recentViolations,
                'recent_positif'    => $recentPositif,
            ],
            'recent_achievements' => $recentAchievements,
        ]);
    }
}
