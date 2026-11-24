<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\ConductLog;
use App\Models\Dispensation;
use App\Models\EarlyCheckoutRequest;
use App\Models\ExitPass;
use App\Models\ForgotAttendanceRequest;
use App\Models\Permit;
use App\Models\PiketSession;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PiketController extends Controller
{
    private function activeTeacher(Request $request): User
    {
        $teacherId = $request->session()->get('piket_teacher_id');
        if ($teacherId) {
            $teacher = User::where('id', $teacherId)->where('role', 'guru')->first();
            if ($teacher) {
                return $teacher;
            }
        }

        /** @var User $user */
        $user = $request->user() ?? Auth::user();
        return $user;
    }

    private function recordPiketAction(string $action, Model $subject, User $teacher, array $extra = []): void
    {
        ActivityLog::record($action, $subject, array_merge([
            'via_piket_account'  => true,
            'piket_teacher_id'   => $teacher->id,
            'piket_teacher_name' => $teacher->name,
        ], $extra));
    }

    // ─── Dashboard Piket ──────────────────────────────────────────────────────

    public function dashboard(Request $request): View
    {
        $teacher = $this->activeTeacher($request);
        $today = now()->toDateString();

        $pendingPermits          = Permit::where('status', 'pending')->count();
        $pendingEarlyCheckouts   = EarlyCheckoutRequest::where('status', 'pending')->count();
        $pendingForgotAttendance = ForgotAttendanceRequest::where('status', 'pending')->count();
        $pendingLateReports      = ConductLog::where('is_self_reported', true)
            ->where('status', 'pending')
            ->whereDate('created_at', $today)
            ->count();
        $activeExitPasses        = ExitPass::where('status', 'out')->count();

        $todayAttendance = [
            'hadir'      => Attendance::whereDate('date', $today)->where('status', 'hadir')->count(),
            'terlambat'  => Attendance::whereDate('date', $today)->where('status', 'terlambat')->count(),
            'izin'       => Attendance::whereDate('date', $today)->where('status', 'izin')->count(),
            'sakit'      => Attendance::whereDate('date', $today)->where('status', 'sakit')->count(),
            'dispensasi' => Attendance::whereDate('date', $today)->where('status', 'dispensasi')->count(),
        ];

        $recentPermits = Permit::with(['student.schoolClass'])
            ->where('status', 'pending')
            ->latest()
            ->limit(6)
            ->get();

        $recentEarlyCheckouts = EarlyCheckoutRequest::with(['student.schoolClass'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        $recentLogs = ActivityLog::with('user:id,name')
            ->whereDate('created_at', $today)
            ->where('action', 'like', 'piket_%')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $todayShifts = PiketSession::with('teacher:id,name,nip')
            ->whereDate('started_at', $today)
            ->latest('started_at')
            ->get();

        return view('piket.dashboard', compact(
            'teacher',
            'pendingPermits',
            'pendingEarlyCheckouts',
            'pendingForgotAttendance',
            'pendingLateReports',
            'activeExitPasses',
            'todayAttendance',
            'recentPermits',
            'recentEarlyCheckouts',
            'recentLogs',
            'todayShifts'
        ));
    }

    // ─── Verifikasi Izin Tidak Masuk Sekolah ──────────────────────────────────

    public function permits(Request $request): View
    {
        $teacher = $this->activeTeacher($request);
        $status  = $request->input('status', 'pending');
        $type    = $request->input('type', 'all');
        $classId = $request->input('class_id');
        $search  = trim((string) $request->input('q', ''));

        $permits = Permit::with(['student.schoolClass', 'approvedBy:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($type !== 'all' && in_array($type, ['izin', 'sakit', 'dispensasi'], true), fn ($q) => $q->where('type', $type))
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->when($search !== '', fn ($q) => $q->whereHas('student', function ($s) use ($search) {
                $s->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pendingCount = Permit::where('status', 'pending')->count();
        $classes      = SchoolClass::orderBy('name')->get(['id', 'name']);

        return view('piket.permits', compact(
            'teacher', 'permits', 'status', 'type', 'classId', 'search', 'pendingCount', 'classes'
        ));
    }

    public function approvePermit(Request $request, Permit $permit): RedirectResponse
    {
        if (! $permit->isPending()) {
            abort(403, 'Pengajuan izin ini sudah diproses.');
        }

        $teacher = $this->activeTeacher($request);

        DB::transaction(function () use ($permit, $teacher) {
            $permit->update([
                'status'      => 'approved',
                'approved_by' => $teacher->id,
            ]);

            $current = $permit->start_date->copy();
            while ($current->lte($permit->end_date)) {
                if (! $current->isSunday()) {
                    $this->syncStudentAttendance($permit->student_id, $current->toDateString(), [
                        'status'        => $permit->type,
                        'check_in_time' => null,
                    ]);
                }
                $current->addDay();
            }
        });

        NotificationService::send(
            $permit->student_id,
            "{$permit->typeLabel()} Disetujui",
            "Pengajuan {$permit->typeLabel()} kamu tanggal {$permit->start_date->isoFormat('D MMM Y')} telah disetujui oleh {$teacher->name} (Guru Piket).",
            'success',
            route('siswa.permit.index'),
        );

        $this->recordPiketAction('piket_permit_approve', $permit, $teacher, [
            'student_id' => $permit->student_id,
            'type'       => $permit->type,
        ]);

        return back()->with('success', "{$permit->typeLabel()} siswa disetujui atas nama {$teacher->name}.");
    }

    public function rejectPermit(Request $request, Permit $permit): RedirectResponse
    {
        if (! $permit->isPending()) {
            abort(403, 'Pengajuan izin ini sudah diproses.');
        }

        $data = $request->validate([
            'rejection_note' => 'required|string|max:255',
        ]);

        $teacher = $this->activeTeacher($request);

        $permit->update([
            'status'         => 'rejected',
            'approved_by'    => $teacher->id,
            'rejection_note' => $data['rejection_note'],
        ]);

        NotificationService::send(
            $permit->student_id,
            "{$permit->typeLabel()} Ditolak",
            "Pengajuan {$permit->typeLabel()} kamu ditolak oleh {$teacher->name} (Guru Piket). Alasan: {$data['rejection_note']}",
            'warning',
            route('siswa.permit.index'),
        );

        $this->recordPiketAction('piket_permit_reject', $permit, $teacher, [
            'student_id'     => $permit->student_id,
            'rejection_note' => $data['rejection_note'],
        ]);

        return back()->with('success', "{$permit->typeLabel()} ditolak atas nama {$teacher->name}.");
    }

    // ─── Verifikasi Izin Pulang Lebih Awal ────────────────────────────────────

    public function earlyCheckouts(Request $request): View
    {
        $teacher = $this->activeTeacher($request);
        $status  = $request->input('status', 'pending');
        $classId = $request->input('class_id');
        $search  = trim((string) $request->input('q', ''));

        $requests = EarlyCheckoutRequest::with(['student.schoolClass', 'reviewer:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->when($search !== '', fn ($q) => $q->whereHas('student', function ($s) use ($search) {
                $s->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            }))
            ->orderByDesc('date')
            ->orderBy('requested_time')
            ->paginate(20)
            ->withQueryString();

        $pendingCount = EarlyCheckoutRequest::where('status', 'pending')->count();
        $classes      = SchoolClass::orderBy('name')->get(['id', 'name']);

        return view('piket.early-checkouts', compact(
            'teacher', 'requests', 'status', 'classId', 'search', 'pendingCount', 'classes'
        ));
    }

    public function approveEarlyCheckout(Request $request, EarlyCheckoutRequest $earlyCheckout): RedirectResponse
    {
        if (! $earlyCheckout->isPending()) {
            abort(403, 'Pengajuan ini sudah diproses.');
        }

        $data = $request->validate([
            'reviewer_note' => 'nullable|string|max:255',
        ]);

        $teacher = $this->activeTeacher($request);

        DB::transaction(function () use ($earlyCheckout, $teacher, $data) {
            $earlyCheckout->update([
                'status'        => 'approved',
                'reviewed_by'   => $teacher->id,
                'reviewed_at'   => now(),
                'reviewer_note' => $data['reviewer_note'] ?? null,
            ]);

            if ($earlyCheckout->isTanpaAbsen()) {
                $category = $earlyCheckout->absence_category ?? 'izin';
                $this->syncStudentAttendance(
                    $earlyCheckout->student_id,
                    $earlyCheckout->date->toDateString(),
                    ['status' => $category]
                );
            }
        });

        if ($earlyCheckout->isTanpaAbsen()) {
            $category = $earlyCheckout->absence_category ?? 'izin';
            NotificationService::send(
                userId: $earlyCheckout->student_id,
                title:  'Pengajuan Pulang Awal (' . ucfirst($category) . ') Disetujui',
                body:   'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . " disetujui oleh {$teacher->name} (Guru Piket). Status dicatat sebagai " . ucfirst($category) . '.',
                type:   'success',
                url:    route('siswa.early-checkout.index'),
            );
        } else {
            NotificationService::send(
                userId: $earlyCheckout->student_id,
                title:  'Izin Pulang Awal Disetujui',
                body:   'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . ' pukul ' . $earlyCheckout->requestedTimeFormatted() . " telah disetujui oleh {$teacher->name} (Guru Piket).",
                type:   'success',
                url:    route('siswa.early-checkout.index'),
            );
        }

        $this->recordPiketAction('piket_early_checkout_approve', $earlyCheckout, $teacher, [
            'student_id' => $earlyCheckout->student_id,
        ]);

        return back()->with('success', "Izin pulang awal disetujui atas nama {$teacher->name}.");
    }

    public function rejectEarlyCheckout(Request $request, EarlyCheckoutRequest $earlyCheckout): RedirectResponse
    {
        if (! $earlyCheckout->isPending()) {
            abort(403, 'Pengajuan ini sudah diproses.');
        }

        $data = $request->validate([
            'reviewer_note' => 'required|string|max:255',
        ]);

        $teacher = $this->activeTeacher($request);

        $earlyCheckout->update([
            'status'        => 'rejected',
            'reviewed_by'   => $teacher->id,
            'reviewed_at'   => now(),
            'reviewer_note' => $data['reviewer_note'],
        ]);

        NotificationService::send(
            userId: $earlyCheckout->student_id,
            title:  'Izin Pulang Awal Ditolak',
            body:   'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . " ditolak oleh {$teacher->name} (Guru Piket). Alasan: {$data['reviewer_note']}",
            type:   'warning',
            url:    route('siswa.early-checkout.index'),
        );

        $this->recordPiketAction('piket_early_checkout_reject', $earlyCheckout, $teacher, [
            'student_id'    => $earlyCheckout->student_id,
            'reviewer_note' => $data['reviewer_note'],
        ]);

        return back()->with('success', "Izin pulang awal ditolak atas nama {$teacher->name}.");
    }

    // ─── Verifikasi Lupa Presensi ──────────────────────────────────────────────

    public function forgotAttendance(Request $request): View
    {
        $teacher = $this->activeTeacher($request);
        $status  = $request->input('status', 'pending');
        $classId = $request->input('class_id');
        $search  = trim((string) $request->input('q', ''));

        $requests = ForgotAttendanceRequest::with(['student.schoolClass', 'reviewer:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->when($search !== '', fn ($q) => $q->whereHas('student', function ($s) use ($search) {
                $s->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pendingCount = ForgotAttendanceRequest::where('status', 'pending')->count();
        $classes      = SchoolClass::orderBy('name')->get(['id', 'name']);

        return view('piket.forgot-attendance', compact(
            'teacher', 'requests', 'status', 'classId', 'search', 'pendingCount', 'classes'
        ));
    }

    public function approveForgotAttendance(Request $request, ForgotAttendanceRequest $forgotAttendance): RedirectResponse
    {
        if (! $forgotAttendance->isPending()) {
            abort(403, 'Pengajuan ini sudah diproses.');
        }

        $data = $request->validate([
            'teacher_note' => 'nullable|string|max:255',
        ]);

        $teacher = $this->activeTeacher($request);

        DB::transaction(function () use ($forgotAttendance, $teacher, $data) {
            $type = $forgotAttendance->type ?: 'masuk';
            $att  = Attendance::where('user_id', $forgotAttendance->student_id)
                ->whereDate('date', $forgotAttendance->date)
                ->first() ?? new Attendance([
                    'user_id' => $forgotAttendance->student_id,
                    'date'    => $forgotAttendance->date->toDateString(),
                ]);

            $att->status          = 'hadir';
            $att->via_lupa_absen  = true;
            $att->lupa_absen_type = $type;

            if ($type === 'masuk') {
                if (! $att->check_in_time) {
                    $att->check_in_time = '07:00:00';
                }
            } elseif ($type === 'pulang') {
                if (! $att->check_out_time) {
                    $att->check_out_time = '15:30:00';
                }
            } else {
                if (! $att->check_in_time) {
                    $att->check_in_time = '07:00:00';
                }
                if (! $att->check_out_time) {
                    $att->check_out_time = '15:30:00';
                }
            }
            $att->save();

            $forgotAttendance->update([
                'status'       => 'approved',
                'reviewed_by'  => $teacher->id,
                'reviewed_at'  => now(),
                'teacher_note' => $data['teacher_note'] ?? null,
            ]);
        });

        NotificationService::send(
            userId: $forgotAttendance->student_id,
            title:  'Lupa Absen Disetujui',
            body:   'Pengajuan ' . $forgotAttendance->typeLabel() . ' tanggal ' . $forgotAttendance->date->isoFormat('D MMMM Y') . " disetujui oleh {$teacher->name} (Guru Piket). Presensi dicatat sebagai Hadir.",
            type:   'success',
            url:    route('siswa.forgot-attendance.index'),
        );

        $this->recordPiketAction('piket_forgot_attendance_approve', $forgotAttendance, $teacher, [
            'student_id' => $forgotAttendance->student_id,
        ]);

        return back()->with('success', "Lupa presensi disetujui atas nama {$teacher->name}.");
    }

    public function rejectForgotAttendance(Request $request, ForgotAttendanceRequest $forgotAttendance): RedirectResponse
    {
        if (! $forgotAttendance->isPending()) {
            abort(403, 'Pengajuan ini sudah diproses.');
        }

        $data = $request->validate([
            'teacher_note' => 'required|string|max:255',
        ]);

        $teacher = $this->activeTeacher($request);

        $forgotAttendance->update([
            'status'       => 'rejected',
            'reviewed_by'  => $teacher->id,
            'reviewed_at'  => now(),
            'teacher_note' => $data['teacher_note'],
        ]);

        NotificationService::send(
            userId: $forgotAttendance->student_id,
            title:  'Lupa Absen Ditolak',
            body:   'Pengajuan lupa absen tanggal ' . $forgotAttendance->date->isoFormat('D MMMM Y') . " ditolak oleh {$teacher->name} (Guru Piket). Alasan: {$data['teacher_note']}",
            type:   'warning',
            url:    route('siswa.forgot-attendance.index'),
        );

        $this->recordPiketAction('piket_forgot_attendance_reject', $forgotAttendance, $teacher, [
            'student_id'   => $forgotAttendance->student_id,
            'teacher_note' => $data['teacher_note'],
        ]);

        return back()->with('success', "Lupa presensi ditolak atas nama {$teacher->name}.");
    }

    // ─── Piket Gerbang / Verifikasi Keterlambatan ────────────────────────────

    public function lateVerification(Request $request): View
    {
        $teacher = $this->activeTeacher($request);
        $date    = $request->input('date', now()->toDateString());
        $status  = $request->input('status', 'pending');
        $classId = $request->input('class_id');
        $search  = trim((string) $request->input('q', ''));

        $logs = ConductLog::with(['student.schoolClass', 'category', 'verifier:id,name'])
            ->where('is_self_reported', true)
            ->whereDate('created_at', $date)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($classId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_id', $classId)))
            ->when($search !== '', fn ($q) => $q->whereHas('student', function ($s) use ($search) {
                $s->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pendingCount  = ConductLog::where('is_self_reported', true)->whereDate('created_at', $date)->where('status', 'pending')->count();
        $verifiedCount = ConductLog::where('is_self_reported', true)->whereDate('created_at', $date)->where('status', 'verified')->count();
        $rejectedCount = ConductLog::where('is_self_reported', true)->whereDate('created_at', $date)->where('status', 'rejected')->count();
        $classes       = SchoolClass::orderBy('name')->get(['id', 'name']);

        return view('piket.late-verification', compact(
            'teacher', 'logs', 'date', 'status', 'classId', 'search',
            'pendingCount', 'verifiedCount', 'rejectedCount', 'classes'
        ));
    }

    public function verifyLate(Request $request, ConductLog $log): RedirectResponse
    {
        if ($log->status === 'verified') {
            return back()->with('info', 'Pengajuan ini sudah diverifikasi sebelumnya.');
        }

        $teacher = $this->activeTeacher($request);

        $log->update([
            'status'         => 'verified',
            'verified_at'    => now(),
            'verifier_id'    => $teacher->id,
            'teacher_id'     => $teacher->id,
            'rejection_note' => null,
        ]);

        $categoryName = method_exists($log, 'displayCategoryName') ? $log->displayCategoryName() : 'Keterlambatan';
        $student = $log->student;

        if ($student) {
            NotificationService::send(
                $student->id,
                'Pembinaan Disiplin Diverifikasi',
                "Pengajuan pembinaan ({$categoryName}) Anda telah diverifikasi oleh {$teacher->name} (Guru Piket). Selamat belajar!",
                'success',
                route('siswa.conduct.index')
            );
            NotificationService::notifyParentsOfStudent(
                $student,
                'Catatan Kedisiplinan Siswa',
                "Ananda {$student->name} telah melakukan pengajuan pembinaan ({$categoryName}) dan diverifikasi oleh {$teacher->name} (Guru Piket).",
                'warning'
            );
        }

        $this->recordPiketAction('piket_late_verify', $log, $teacher, [
            'student_id' => $log->student_id,
        ]);

        return back()->with('success', "Pengajuan pembinaan {$student?->name} diverifikasi atas nama {$teacher->name}.");
    }

    public function rejectLate(Request $request, ConductLog $log): RedirectResponse
    {
        $data = $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        $teacher = $this->activeTeacher($request);

        $log->update([
            'status'         => 'rejected',
            'rejection_note' => $data['rejection_note'],
            'verifier_id'    => $teacher->id,
            'verified_at'    => now(),
        ]);

        $student = $log->student;
        if ($student) {
            NotificationService::send(
                $student->id,
                'Pengajuan Pembinaan Dikembalikan',
                "Pengajuan pembinaan Anda dikembalikan oleh {$teacher->name} (Guru Piket). Catatan: {$data['rejection_note']}",
                'warning',
                route('siswa.conduct.index')
            );
        }

        $this->recordPiketAction('piket_late_reject', $log, $teacher, [
            'student_id'     => $log->student_id,
            'rejection_note' => $data['rejection_note'],
        ]);

        return back()->with('success', "Pengajuan pembinaan {$student?->name} dikembalikan atas nama {$teacher->name}.");
    }

    // ─── Input Dispensasi ─────────────────────────────────────────────────────

    public function dispensationForm(Request $request): View
    {
        $teacher = $this->activeTeacher($request);
        $classes = SchoolClass::with(['students' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        $recentDispensations = Dispensation::with(['students:id,name,class_id', 'students.schoolClass:id,name', 'approver:id,name'])
            ->latest()
            ->limit(15)
            ->get();

        return view('piket.dispensation', compact('teacher', 'classes', 'recentDispensations'));
    }

    public function storeDispensation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_name' => 'required|string|max:255',
            'date'          => 'required|date',
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $teacher  = $this->activeTeacher($request);
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('dispensations', 'public');
        }

        $dispensation = DB::transaction(function () use ($data, $teacher, $filePath) {
            $created = Dispensation::create([
                'requester_id'  => $teacher->id,
                'activity_name' => $data['activity_name'],
                'date'          => $data['date'],
                'file'          => $filePath,
                'status'        => 'approved',
                'approved_by'   => $teacher->id,
            ]);

            $created->students()->attach($data['student_ids']);

            foreach ($data['student_ids'] as $studentId) {
                $this->syncStudentAttendance((int) $studentId, $data['date'], [
                    'status'        => 'dispensasi',
                    'check_in_time' => null,
                ]);
            }

            return $created;
        });

        $this->recordPiketAction('piket_dispensation_create', $dispensation, $teacher, [
            'activity_name' => $data['activity_name'],
            'date'          => $data['date'],
            'student_count' => count($data['student_ids']),
        ]);

        return back()->with('success', 'Dispensasi untuk ' . count($data['student_ids']) . " siswa berhasil dicatat atas nama {$teacher->name}.");
    }

    // ─── Pantau Izin Keluar Kelas (Exit Pass) ────────────────────────────────

    public function exitPasses(Request $request): View
    {
        $teacher = $this->activeTeacher($request);
        $date    = $request->input('date', now()->toDateString());
        $status  = $request->input('status', 'all');

        $activePasses = ExitPass::with('student.schoolClass')
            ->where('status', 'out')
            ->latest('out_time')
            ->get();

        $passes = ExitPass::with('student.schoolClass')
            ->whereDate('out_time', $date)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest('out_time')
            ->paginate(20)
            ->withQueryString();

        return view('piket.exit-passes', compact('teacher', 'activePasses', 'passes', 'date', 'status'));
    }

    public function markExitPassReturned(Request $request, ExitPass $exitPass): RedirectResponse
    {
        if ($exitPass->status !== 'out') {
            return back()->with('info', 'Siswa ini sudah tercatat kembali ke kelas.');
        }

        $teacher = $this->activeTeacher($request);
        $exitPass->update([
            'in_time' => now(),
            'status'  => 'returned',
        ]);

        $this->recordPiketAction('piket_exit_pass_return', $exitPass, $teacher, [
            'student_id' => $exitPass->student_id,
        ]);

        return back()->with('success', "Siswa {$exitPass->student?->name} ditandai kembali ke kelas oleh {$teacher->name}.");
    }

    // ─── Riwayat Shift & Log Piket ────────────────────────────────────────────

    public function logs(Request $request): View
    {
        $teacher = $this->activeTeacher($request);
        $date    = $request->input('date');

        $shifts = PiketSession::with('teacher:id,name,nip')
            ->when($date, fn ($q) => $q->whereDate('started_at', $date))
            ->latest('started_at')
            ->paginate(15, ['*'], 'shift_page')
            ->withQueryString();

        $activityLogs = ActivityLog::with('user:id,name')
            ->where('action', 'like', 'piket_%')
            ->when($date, fn ($q) => $q->whereDate('created_at', $date))
            ->latest('created_at')
            ->paginate(25, ['*'], 'log_page')
            ->withQueryString();

        return view('piket.logs', compact('teacher', 'date', 'shifts', 'activityLogs'));
    }

    private function syncStudentAttendance(int $studentId, string $dateStr, array $attributes): Attendance
    {
        $att = Attendance::where('user_id', $studentId)
            ->whereDate('date', $dateStr)
            ->first();

        if ($att) {
            $att->update($attributes);
            return $att;
        }

        return Attendance::create(array_merge([
            'user_id' => $studentId,
            'date'    => $dateStr,
        ], $attributes));
    }
}
