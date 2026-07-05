<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\BkConsultation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BkConsultationController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = auth()->user();

        $consultations = BkConsultation::where('student_id', $siswa->id)
            ->with('teacher:id,name')
            ->latest()
            ->get();

        $active = $consultations->whereIn('status', ['pending', 'scheduled'])->first();

        $bkTeachers = User::where('role', 'guru')
            ->where(fn($q) => $q
                ->whereRaw("LOWER(subject) LIKE '%bk%'")
                ->orWhereHas('subjects', fn($q2) => $q2->whereRaw("LOWER(name) LIKE '%bk%'"))
            )
            ->orderBy('name')
            ->get();

        return view('siswa.bk-consultation.index', compact('consultations', 'active', 'bkTeachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = auth()->user();

        $hasActive = BkConsultation::where('student_id', $siswa->id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->exists();

        if ($hasActive) {
            return back()->with('error', 'Anda masih memiliki pengajuan bimbingan BK yang belum selesai.');
        }

        $data    = $request->validate([
            'teacher_id'   => 'required|exists:users,id',
            'topic'        => 'required|string|max:200',
            'student_note' => 'nullable|string|max:1000',
        ]);
        $teacher = User::findOrFail($data['teacher_id']);
        if (! $teacher->isBk()) {
            return back()->with('error', 'Guru yang dipilih bukan Guru BK.');
        }

        BkConsultation::create([
            'student_id'   => $siswa->id,
            'teacher_id'   => $data['teacher_id'],
            'topic'        => $data['topic'],
            'student_note' => $data['student_note'] ?? null,
        ]);

        return back()->with('success', 'Pengajuan bimbingan BK berhasil dikirim.');
    }

    public function changeTeacher(Request $request, BkConsultation $consultation): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = auth()->user();
        abort_unless($consultation->student_id === $siswa->id, 403);
        abort_unless($consultation->isPending(), 403);

        $data    = $request->validate(['teacher_id' => 'required|exists:users,id']);
        $teacher = User::findOrFail($data['teacher_id']);
        if (! $teacher->isBk()) {
            return back()->with('error', 'Guru yang dipilih bukan Guru BK.');
        }

        $consultation->update(['teacher_id' => $data['teacher_id']]);

        return back()->with('success', 'Guru BK berhasil diganti.');
    }

    public function cancel(BkConsultation $consultation): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = auth()->user();
        abort_unless($consultation->student_id === $siswa->id && $consultation->isPending(), 403);

        $consultation->update(['status' => 'cancelled', 'cancelled_reason' => 'Dibatalkan oleh siswa']);

        return back()->with('success', 'Pengajuan bimbingan BK dibatalkan.');
    }
}
