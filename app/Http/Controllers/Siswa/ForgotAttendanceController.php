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
        $studentId = Auth::id();
        $requests  = ForgotAttendanceRequest::where('student_id', $studentId)
            ->latest()
            ->paginate(10);
        $quota = ForgotAttendanceRequest::monthlySummaryForStudent($studentId);

        return view('siswa.forgot-attendance.index', compact('requests', 'quota'));
    }

    public function create(): View
    {
        $quota = ForgotAttendanceRequest::monthlySummaryForStudent(Auth::id());

        return view('siswa.forgot-attendance.create', compact('quota'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'   => 'required|string|in:masuk,pulang,keduanya',
            'date'   => [
                'required', 'date',
                'before_or_equal:today',
                'after_or_equal:' . now()->subDays(30)->toDateString(),
            ],
            'reason' => 'required|string|max:500',
        ]);

        $student = Auth::user()->load('schoolClass.homeroomTeacher');

        $targetType = $data['type'] ?? 'masuk';
        $targetDate = Carbon::parse($data['date']);

        // Pengecekan pengajuan yang sudah ada berdasarkan jenis (masuk / pulang / keduanya)
        $existingQuery = ForgotAttendanceRequest::where('student_id', $student->id)
            ->whereDate('date', $data['date'])
            ->whereIn('status', ['pending', 'approved']);

        if ($targetType === 'masuk') {
            $existing = (clone $existingQuery)->whereIn('type', ['masuk', 'keduanya'])->first();
        } elseif ($targetType === 'pulang') {
            $existing = (clone $existingQuery)->whereIn('type', ['pulang', 'keduanya'])->first();
        } else {
            $existing = (clone $existingQuery)->first();
        }

        if ($existing) {
            $typeLabel = match ($targetType) {
                'masuk'   => 'datang',
                'pulang'  => 'pulang',
                default   => 'datang & pulang',
            };
            return back()->withErrors(['date' => "Sudah ada pengajuan lupa absen {$typeLabel} untuk tanggal ini."])->withInput();
        }

        // Cek batas kuota bulanan (maksimal 10x per bulan, direset setiap pergantian bulan).
        // Absen datang, absen pulang, maupun semua absen (datang & pulang) dihitung 1x per tanggal pengajuan.
        $alreadyHasRequestOnDate = (clone $existingQuery)->exists();
        if (! $alreadyHasRequestOnDate) {
            $usedInTargetMonth = ForgotAttendanceRequest::monthlyUsedCount(
                $student->id,
                (int) $targetDate->year,
                (int) $targetDate->month
            );

            if ($usedInTargetMonth >= ForgotAttendanceRequest::MONTHLY_LIMIT) {
                $monthLabel = $targetDate->isoFormat('MMMM Y');
                return back()->withErrors([
                    'date' => "Kuota pengajuan lupa absen Anda untuk bulan {$monthLabel} sudah habis (maksimal " . ForgotAttendanceRequest::MONTHLY_LIMIT . "x per bulan). Kuota di-reset setiap pergantian bulan.",
                ])->withInput();
            }
        }

        // Pengecekan data presensi yang sudah tercatat
        $attendance = Attendance::where('user_id', $student->id)
            ->whereDate('date', $data['date'])
            ->first();

        if ($attendance && $attendance->status !== 'alpa') {
            if ($targetType === 'masuk' && $attendance->check_in_time) {
                return back()->withErrors(['date' => 'Presensi masuk/datang untuk tanggal ini sudah tercatat.'])->withInput();
            }
            if ($targetType === 'pulang' && $attendance->check_out_time) {
                return back()->withErrors(['date' => 'Presensi pulang untuk tanggal ini sudah tercatat.'])->withInput();
            }
            if ($targetType === 'keduanya' && $attendance->check_in_time && $attendance->check_out_time) {
                return back()->withErrors(['date' => 'Presensi datang & pulang untuk tanggal ini sudah tercatat lengkap.'])->withInput();
            }
        }

        ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'type'       => $data['type'] ?? 'masuk',
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
