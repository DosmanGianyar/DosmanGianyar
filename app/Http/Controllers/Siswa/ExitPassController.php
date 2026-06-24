<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ExitPass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExitPassController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $siswa = Auth::user();

        // Cek apakah masih ada exit pass yang belum kembali
        $active = ExitPass::where('student_id', $siswa->id)
            ->where('status', 'out')
            ->latest()
            ->first();

        return view('siswa.exit-pass', compact('siswa', 'active'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'reason'        => 'required|in:toilet,uks,other',
            'reason_detail' => 'nullable|string|max:100',
        ]);

        // Pastikan tidak ada exit pass aktif
        $existing = ExitPass::where('student_id', Auth::id())
            ->where('status', 'out')
            ->exists();

        if ($existing) {
            return back()->withErrors(['reason' => 'Kamu masih memiliki izin keluar yang aktif.']);
        }

        ExitPass::create([
            'student_id'    => Auth::id(),
            'reason'        => $request->reason,
            'reason_detail' => $request->reason_detail,
            'out_time'      => now(),
            'status'        => 'out',
        ]);

        return redirect()->route('siswa.exit-pass.show')
            ->with('success', 'Izin keluar berhasil dicatat. Timer berjalan.');
    }

    public function checkin(): RedirectResponse
    {
        $pass = ExitPass::where('student_id', Auth::id())
            ->where('status', 'out')
            ->latest()
            ->first();

        if (!$pass) {
            return back()->withErrors(['error' => 'Tidak ada izin keluar yang aktif.']);
        }

        $pass->update(['in_time' => now(), 'status' => 'returned']);

        $duration = $pass->duration_minutes;
        return redirect()->route('siswa.dashboard')
            ->with('success', "Berhasil kembali ke kelas. Durasi keluar: {$duration} menit.");
    }
}
