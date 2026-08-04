<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $extracurriculars = Extracurricular::active()
            ->with(['pembina:id,name', 'activeMembers'])
            ->withCount('activeMembers')
            ->orderBy('name')
            ->get();

        // Status keanggotaan siswa ini di setiap ekstra
        $myMemberships = ExtracurricularMember::where('user_id', $siswa->id)
            ->pluck('status', 'extracurricular_id');

        return view('siswa.extracurricular.index', compact('extracurriculars', 'myMemberships'));
    }

    public function join(Extracurricular $extracurricular): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        if (!$extracurricular->is_active) {
            return back()->with('error', 'Ekstrakurikuler ini tidak aktif.');
        }

        $existing = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
            ->where('user_id', $siswa->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Kamu sudah terdaftar atau memiliki permintaan aktif di ekstra ini.');
        }

        if ($extracurricular->isFull()) {
            return back()->with('error', 'Kuota anggota ekstra ini sudah penuh.');
        }

        ExtracurricularMember::create([
            'extracurricular_id' => $extracurricular->id,
            'user_id'            => $siswa->id,
            'role'               => 'member',
            'status'             => 'pending_join',
        ]);

        return back()->with('success', 'Permintaan bergabung berhasil dikirim. Menunggu persetujuan pembina.');
    }

    public function leave(Extracurricular $extracurricular): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa  = Auth::user();
        $member = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
            ->where('user_id', $siswa->id)
            ->where('status', 'active')
            ->first();

        if (!$member) {
            return back()->with('error', 'Kamu bukan anggota aktif ekstra ini.');
        }

        $member->update(['status' => 'pending_leave']);

        return back()->with('success', 'Permintaan keluar berhasil dikirim. Menunggu persetujuan pembina.');
    }

    public function cancelJoin(Extracurricular $extracurricular): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa  = Auth::user();
        $member = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
            ->where('user_id', $siswa->id)
            ->where('status', 'pending_join')
            ->first();

        if (!$member) {
            return back()->with('error', 'Tidak ada permintaan bergabung yang aktif.');
        }

        $member->delete();

        return back()->with('success', 'Permintaan bergabung dibatalkan.');
    }
}
