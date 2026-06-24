<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\HomeroomConsultation;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeroomConsultationController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = auth()->user();

        $consultations = HomeroomConsultation::where('student_id', $siswa->id)
            ->with('teacher')
            ->latest()
            ->get();

        $homeroomTeacher = $siswa->schoolClass?->homeroomTeacher;

        return view('siswa.homeroom-consultation.index', compact('consultations', 'homeroomTeacher'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = auth()->user();

        $class = SchoolClass::find($siswa->class_id);
        if (! $class?->homeroom_teacher_id) {
            return back()->with('error', 'Kelas Anda belum memiliki wali kelas.');
        }

        // Cegah pengajuan ganda saat masih pending/scheduled
        $hasActive = HomeroomConsultation::where('student_id', $siswa->id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->exists();

        if ($hasActive) {
            return back()->with('error', 'Anda masih memiliki pengajuan bimbingan yang belum selesai.');
        }

        $request->validate([
            'topic'        => 'required|string|max:200',
            'student_note' => 'nullable|string|max:1000',
        ]);

        HomeroomConsultation::create([
            'student_id'   => $siswa->id,
            'teacher_id'   => $class->homeroom_teacher_id,
            'class_id'     => $siswa->class_id,
            'topic'        => $request->topic,
            'student_note' => $request->student_note,
        ]);

        return back()->with('success', 'Pengajuan bimbingan berhasil dikirim.');
    }

    public function cancel(HomeroomConsultation $consultation): RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = auth()->user();
        abort_unless($consultation->student_id === $siswa->id && $consultation->isPending(), 403);

        $consultation->update(['status' => 'cancelled', 'cancelled_reason' => 'Dibatalkan oleh siswa']);

        return back()->with('success', 'Pengajuan bimbingan dibatalkan.');
    }
}
