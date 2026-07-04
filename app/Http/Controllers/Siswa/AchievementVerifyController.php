<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AchievementVerifyController extends Controller
{
    private function checkRole(): void
    {
        if (Auth::user()->role !== 'pengelola') {
            abort(403, 'Hanya Siswa Pengelola yang dapat mengakses halaman ini.');
        }
    }

    public function index(Request $request): View
    {
        $this->checkRole();

        $status = $request->get('status', 'pending');

        $achievements = StudentAchievement::with('student.schoolClass', 'category', 'verifier')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $pendingCount = StudentAchievement::where('status', 'pending')->count();

        return view('siswa.achievement.verify', compact('achievements', 'status', 'pendingCount'));
    }

    public function approve(StudentAchievement $achievement): RedirectResponse
    {
        $this->checkRole();

        if ($achievement->status !== 'pending') {
            return back()->with('success', 'Status sudah diperbarui sebelumnya.');
        }

        $achievement->update([
            'status'           => 'approved',
            'verified_by'      => Auth::id(),
            'verified_at'      => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Prestasi \"{$achievement->title}\" berhasil disetujui.");
    }

    public function reject(Request $request, StudentAchievement $achievement): RedirectResponse
    {
        $this->checkRole();

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($achievement->status !== 'pending') {
            return back()->with('success', 'Status sudah diperbarui sebelumnya.');
        }

        $achievement->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'verified_by'      => Auth::id(),
            'verified_at'      => now(),
        ]);

        return back()->with('success', "Prestasi \"{$achievement->title}\" ditolak.");
    }
}
