<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ForgotAttendanceRequest;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ForgotAttendanceController extends Controller
{
    public function index(): View
    {
        $requests = ForgotAttendanceRequest::where('student_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('siswa.forgot-attendance.index', compact('requests'));
    }

    public function create(): View
    {
        return view('siswa.forgot-attendance.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date'   => [
                'required', 'date',
                'before_or_equal:today',
                'after_or_equal:' . now()->subDays(30)->toDateString(),
            ],
            'reason' => 'required|string|max:500',
        ]);

        $student = Auth::user()->load('schoolClass.homeroomTeacher');

        // Reject if a pending/approved request already exists for this date
        $existing = ForgotAttendanceRequest::where('student_id', $student->id)
            ->where('date', $data['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return back()->withErrors(['date' => 'Sudah ada pengajuan lupa absen untuk tanggal ini.'])->withInput();
        }

        // Reject if attendance already recorded with a non-alpa status
        $attendance = Attendance::where('user_id', $student->id)
            ->whereDate('date', $data['date'])
            ->first();

        if ($attendance && $attendance->status !== 'alpa') {
            return back()->withErrors([
                'date' => 'Presensi tanggal ini sudah tercatat sebagai ' . $attendance->status_label . '.',
            ])->withInput();
        }

        ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'date'       => $data['date'],
            'reason'     => $data['reason'],
            'status'     => 'pending',
        ]);

        // Notify homeroom teacher
        $homeroomTeacher = $student->schoolClass?->homeroomTeacher;
        if ($homeroomTeacher) {
            NotificationService::send(
                userId: $homeroomTeacher->id,
                title:  'Pengajuan Lupa Absen',
                body:   $student->name . ' mengajukan lupa absen pada ' . Carbon::parse($data['date'])->isoFormat('D MMMM Y'),
                type:   'info',
                url:    route('guru.forgot-attendance.index'),
            );
        }

        return redirect()->route('siswa.forgot-attendance.index')
            ->with('success', 'Pengajuan lupa absen berhasil dikirim. Menunggu persetujuan wali kelas.');
    }

    public function destroy(ForgotAttendanceRequest $forgotAttendance): RedirectResponse
    {
        if ($forgotAttendance->student_id !== Auth::id() || ! $forgotAttendance->isPending()) {
            abort(403, 'Tidak dapat membatalkan pengajuan ini.');
        }

        $forgotAttendance->delete();

        return redirect()->route('siswa.forgot-attendance.index')
            ->with('success', 'Pengajuan berhasil dibatalkan.');
    }
}
