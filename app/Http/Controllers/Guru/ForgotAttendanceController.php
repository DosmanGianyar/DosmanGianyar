<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ForgotAttendanceRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ForgotAttendanceController extends Controller
{
    public function index(): View
    {
        $guru = Auth::user();
        $homeroomClass = $guru->homeroomClass;

        $requests = $homeroomClass
            ? ForgotAttendanceRequest::with(['student.schoolClass'])
                ->whereHas('student', fn($q) => $q->where('class_id', $homeroomClass->id))
                ->latest()
                ->paginate(15)
            : collect()->paginate(15);

        return view('guru.forgot-attendance.index', compact('requests', 'homeroomClass'));
    }

    public function approve(Request $request, ForgotAttendanceRequest $forgotAttendance): RedirectResponse
    {
        $this->authorizeReview($forgotAttendance);

        $data = $request->validate([
            'teacher_note' => 'nullable|string|max:255',
        ]);

        // Create or update attendance as 'hadir'
        Attendance::updateOrCreate(
            ['user_id' => $forgotAttendance->student_id, 'date' => $forgotAttendance->date->toDateString()],
            ['status' => 'hadir']
        );

        $forgotAttendance->update([
            'status'       => 'approved',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'teacher_note' => $data['teacher_note'] ?? null,
        ]);

        NotificationService::send(
            userId: $forgotAttendance->student_id,
            title:  'Lupa Absen Disetujui',
            body:   'Pengajuan lupa absen tanggal ' . $forgotAttendance->date->isoFormat('D MMMM Y') . ' telah disetujui. Presensi dicatat sebagai Hadir.',
            type:   'success',
            url:    route('siswa.forgot-attendance.index'),
        );

        return back()->with('success', 'Pengajuan disetujui. Presensi siswa dicatat sebagai Hadir.');
    }

    public function reject(Request $request, ForgotAttendanceRequest $forgotAttendance): RedirectResponse
    {
        $this->authorizeReview($forgotAttendance);

        $data = $request->validate([
            'teacher_note' => 'required|string|max:255',
        ]);

        $forgotAttendance->update([
            'status'       => 'rejected',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'teacher_note' => $data['teacher_note'],
        ]);

        NotificationService::send(
            userId: $forgotAttendance->student_id,
            title:  'Lupa Absen Ditolak',
            body:   'Pengajuan lupa absen tanggal ' . $forgotAttendance->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $data['teacher_note'],
            type:   'warning',
            url:    route('siswa.forgot-attendance.index'),
        );

        return back()->with('success', 'Pengajuan ditolak.');
    }

    private function authorizeReview(ForgotAttendanceRequest $forgotAttendance): void
    {
        $homeroomClass = Auth::user()->homeroomClass;

        if (! $homeroomClass) {
            abort(403, 'Anda bukan wali kelas.');
        }

        if ($forgotAttendance->student->class_id !== $homeroomClass->id) {
            abort(403, 'Siswa bukan anggota kelas Anda.');
        }

        if (! $forgotAttendance->isPending()) {
            abort(403, 'Pengajuan ini sudah diproses.');
        }
    }
}
