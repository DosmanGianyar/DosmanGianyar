<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EarlyCheckoutRequest;
use App\Models\ForgotAttendanceRequest;
use App\Models\Holiday;
use App\Models\Permit;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuruController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard(): JsonResponse
    {
        /** @var \App\Models\User $guru */
        $guru = Auth::user();
        $guru->load('homeroomClass');
        $classId = $guru->homeroomClass?->id;

        $totalStudents = $classId
            ? User::where('role', 'siswa')->where('class_id', $classId)->count()
            : 0;

        $isPrivileged = $guru->isBk() || $guru->role === 'admin';

        $pendingPermits = Permit::where('status', 'pending')
            ->when(! $isPrivileged && $classId, fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', $classId)))
            ->when(! $isPrivileged && ! $classId, fn($q) => $q->whereRaw('0=1'))
            ->count();

        $pendingEarlyCheckouts = EarlyCheckoutRequest::where('status', 'pending')->count();

        $pendingForgotAttendances = ForgotAttendanceRequest::where('status', 'pending')
            ->when(! $isPrivileged && $classId, fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', $classId)))
            ->when(! $isPrivileged && ! $classId, fn($q) => $q->whereRaw('0=1'))
            ->count();

        $alerts = User::where('role', 'siswa')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->withCount(['conductLogs as pelanggaran_count' => fn($q) => $q->whereHas('category', fn($c) => $c->where('type', 'pelanggaran'))])
            ->having('pelanggaran_count', '>', 0)
            ->orderByDesc('pelanggaran_count')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'name'              => $s->name,
                'class'             => $s->schoolClass?->name ?? '—',
                'pelanggaran_count' => $s->pelanggaran_count,
            ]);

        return response()->json([
            'total_students'              => $totalStudents,
            'pending_permits'             => $pendingPermits,
            'pending_early_checkouts'     => $pendingEarlyCheckouts,
            'pending_forgot_attendances'  => $pendingForgotAttendances,
            'recent_alerts'               => $alerts,
        ]);
    }

    // ─── Kelas ────────────────────────────────────────────────────────────────

    public function classes(): JsonResponse
    {
        $classes = SchoolClass::orderBy('name')
            ->withCount(['students' => fn($q) => $q->where('role', 'siswa')])
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'grade'         => $c->grade,
                'student_count' => $c->students_count,
            ]);

        return response()->json(['classes' => $classes]);
    }

    // ─── Absensi Harian ───────────────────────────────────────────────────────

    public function attendanceDaily(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date'     => 'required|date',
        ]);

        $classId = (int) $request->input('class_id');
        $date    = $request->input('date');

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->with(['attendances' => fn($q) => $q->whereDate('date', $date)])
            ->orderBy('name')
            ->get();

        $studentIds = $students->pluck('id')->all();
        $approvedEarlyCheckouts = EarlyCheckoutRequest::whereIn('student_id', $studentIds)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->pluck('student_id')
            ->mapWithKeys(fn($id) => [$id => true])
            ->all();

        $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'dispensasi' => 0];
        $rows = [];

        foreach ($students as $student) {
            $att              = $student->attendances->first();
            $hasEarlyApproval = isset($approvedEarlyCheckouts[$student->id]);
            $status           = $att ? $att->effectiveStatus($hasEarlyApproval) : 'alpa';
            if (isset($summary[$status])) $summary[$status]++;
            $rows[] = [
                'id'            => $student->id,
                'name'          => $student->name,
                'nis'           => $student->nis,
                'status'        => $status,
                'check_in_time' => $att?->check_in_time,
                'has_early_checkout' => $hasEarlyApproval,
            ];
        }

        return response()->json([
            'date'    => $date,
            'class_id' => $classId,
            'summary' => $summary,
            'students' => $rows,
        ]);
    }

    // ─── Rekap Absensi ────────────────────────────────────────────────────────

    public function attendanceRekap(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'month'    => 'required|integer|min:1|max:12',
            'year'     => 'required|integer|min:2020',
        ]);

        $classId = (int) $request->input('class_id');
        $month   = (int) $request->input('month');
        $year    = (int) $request->input('year');
        $today   = today();

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $holidays    = Holiday::getHolidayDates($start, $end, $classId);
        $specialDays = Holiday::getSpecialSchoolDates($start, $end, $classId);

        $allDays    = [];
        $schoolDays = [];
        $offDays    = [];
        $cursor     = $start->copy();
        while ($cursor->lte($end)) {
            $dateStr = $cursor->format('Y-m-d');
            $allDays[] = $dateStr;
            if (Holiday::isSchoolDay($cursor, $holidays, $specialDays)) {
                $schoolDays[] = $dateStr;
            } else {
                $offDays[$dateStr] = true;
            }
            $cursor->addDay();
        }

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get();

        $attendances = Attendance::whereHas('student', fn($q) => $q->where('class_id', $classId))
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        $approvedEarlyCheckouts = EarlyCheckoutRequest::whereIn('student_id', $students->pluck('id'))
            ->whereBetween('date', [$start, $end])
            ->where('status', 'approved')
            ->get(['student_id', 'date'])
            ->groupBy('student_id')
            ->map(fn($g) => $g->mapWithKeys(fn($r) => [$r->date->format('Y-m-d') => true])->all());

        $studentData = $students->map(function ($student) use ($attendances, $schoolDays, $approvedEarlyCheckouts, $offDays, $today) {
            $recs             = $attendances->get($student->id, collect())->keyBy(fn($a) => $a->date->format('Y-m-d'));
            $studentApprovals = $approvedEarlyCheckouts->get($student->id, []);
            $counts           = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'dispensasi' => 0];
            $statuses         = [];

            foreach ($schoolDays as $dateStr) {
                $day = Carbon::parse($dateStr);
                if ($day->gt($today)) {
                    $statuses[$dateStr] = 'future';
                    continue;
                }
                $att              = $recs->get($dateStr);
                $hasEarlyApproval = isset($studentApprovals[$dateStr]);
                $status           = $att ? $att->effectiveStatus($hasEarlyApproval) : 'alpa';
                $statuses[$dateStr] = $status;
                if (isset($counts[$status])) $counts[$status]++;
            }

            return [
                'id'       => $student->id,
                'name'     => $student->name,
                'nis'      => $student->nis,
                'statuses' => $statuses,
                'counts'   => $counts,
            ];
        });

        return response()->json([
            'class_id'    => $classId,
            'month'       => $month,
            'year'        => $year,
            'all_days'    => $allDays,
            'school_days' => $schoolDays,
            'off_days'    => array_keys($offDays),
            'students'    => $studentData->values(),
        ]);
    }

    // ─── Izin / Sakit / Dispensasi ────────────────────────────────────────────

    public function permits(Request $request): JsonResponse
    {
        /** @var \App\Models\User $guru */
        $guru   = Auth::user();
        $guru->load('homeroomClass');
        $status = $request->input('status', 'pending');
        $page   = (int) $request->input('page', 1);

        $query = Permit::with('student.schoolClass')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when(
                ! $guru->isBk() && $guru->role !== 'admin' && $guru->homeroomClass,
                fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', $guru->homeroomClass->id))
            )
            ->when(
                ! $guru->isBk() && $guru->role !== 'admin' && ! $guru->homeroomClass,
                fn($q) => $q->whereIn('status', ['approved', 'rejected'])
            )
            ->latest();

        $paginated = $query->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->map(fn($p) => $this->permitPayload($p)),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function approvePermit(Permit $permit): JsonResponse
    {
        $this->authorizePermitAction($permit);

        $permit->update(['status' => 'approved', 'approved_by' => Auth::id()]);
        $this->syncPermitAttendance($permit);

        NotificationService::send(
            $permit->student_id,
            "{$permit->typeLabel()} Disetujui",
            "Pengajuan {$permit->typeLabel()} kamu untuk tanggal {$permit->start_date->isoFormat('D MMM Y')} telah disetujui.",
            'success',
        );

        return response()->json(['message' => "{$permit->typeLabel()} disetujui."]);
    }

    public function rejectPermit(Request $request, Permit $permit): JsonResponse
    {
        $this->authorizePermitAction($permit);
        $request->validate(['rejection_note' => 'required|string|max:255']);

        $permit->update([
            'status'         => 'rejected',
            'approved_by'    => Auth::id(),
            'rejection_note' => $request->rejection_note,
        ]);

        NotificationService::send(
            $permit->student_id,
            "{$permit->typeLabel()} Ditolak",
            "Pengajuan {$permit->typeLabel()} kamu ditolak. Alasan: {$request->rejection_note}",
            'warning',
        );

        return response()->json(['message' => "{$permit->typeLabel()} ditolak."]);
    }

    // ─── Lupa Absen ───────────────────────────────────────────────────────────

    public function forgotAttendance(Request $request): JsonResponse
    {
        /** @var \App\Models\User $guru */
        $guru  = Auth::user();
        $guru->load('homeroomClass');
        $status = $request->input('status', 'pending');
        $page   = (int) $request->input('page', 1);

        $query = ForgotAttendanceRequest::with('student.schoolClass')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when(
                ! $guru->isBk() && $guru->role !== 'admin' && $guru->homeroomClass,
                fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', $guru->homeroomClass->id))
            )
            ->when(
                ! $guru->isBk() && $guru->role !== 'admin' && ! $guru->homeroomClass,
                fn($q) => $q->whereIn('status', ['approved', 'rejected'])
            )
            ->latest();

        $paginated = $query->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->map(fn($r) => [
                'id'           => $r->id,
                'student_name' => $r->student?->name ?? '—',
                'class_name'   => $r->student?->schoolClass?->name ?? '—',
                'date'         => $r->date?->toDateString(),
                'reason'       => $r->reason,
                'status'       => $r->status,
                'teacher_note' => $r->teacher_note,
                'reviewed_at'  => $r->reviewed_at?->toDateTimeString(),
            ]),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function approveForgotAttendance(ForgotAttendanceRequest $forgotAttendance): JsonResponse
    {
        $this->authorizeForgotAttendance($forgotAttendance);

        Attendance::updateOrCreate(
            ['user_id' => $forgotAttendance->student_id, 'date' => $forgotAttendance->date->toDateString()],
            ['status' => 'hadir']
        );

        $forgotAttendance->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        NotificationService::send(
            $forgotAttendance->student_id,
            'Lupa Absen Disetujui',
            'Pengajuan lupa absen tanggal ' . $forgotAttendance->date->isoFormat('D MMMM Y') . ' telah disetujui.',
            'success',
        );

        return response()->json(['message' => 'Disetujui. Presensi dicatat sebagai Hadir.']);
    }

    public function rejectForgotAttendance(Request $request, ForgotAttendanceRequest $forgotAttendance): JsonResponse
    {
        $this->authorizeForgotAttendance($forgotAttendance);
        $request->validate(['teacher_note' => 'required|string|max:255']);

        $forgotAttendance->update([
            'status'       => 'rejected',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'teacher_note' => $request->teacher_note,
        ]);

        NotificationService::send(
            $forgotAttendance->student_id,
            'Lupa Absen Ditolak',
            'Pengajuan lupa absen tanggal ' . $forgotAttendance->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $request->teacher_note,
            'warning',
        );

        return response()->json(['message' => 'Pengajuan ditolak.']);
    }

    // ─── Pulang Lebih Awal ────────────────────────────────────────────────────

    public function earlyCheckouts(Request $request): JsonResponse
    {
        $status = $request->input('status', 'pending');
        $page   = (int) $request->input('page', 1);

        $query = EarlyCheckoutRequest::with('student.schoolClass')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByDesc('date')
            ->orderBy('requested_time');

        $paginated = $query->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->map(fn($r) => [
                'id'             => $r->id,
                'student_name'   => $r->student?->name ?? '—',
                'class_name'     => $r->student?->schoolClass?->name ?? '—',
                'date'           => $r->date?->toDateString(),
                'requested_time' => $r->requested_time,
                'reason'         => $r->reason,
                'status'         => $r->status,
                'reviewer_note'  => $r->reviewer_note,
                'reviewed_at'    => $r->reviewed_at?->toDateTimeString(),
            ]),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function approveEarlyCheckout(Request $request, EarlyCheckoutRequest $earlyCheckout): JsonResponse
    {
        if (! $earlyCheckout->isPending()) {
            return response()->json(['message' => 'Pengajuan ini sudah diproses.'], 403);
        }

        $data = $request->validate(['reviewer_note' => 'nullable|string|max:255']);

        $earlyCheckout->update([
            'status'        => 'approved',
            'reviewed_by'   => Auth::id(),
            'reviewed_at'   => now(),
            'reviewer_note' => $data['reviewer_note'] ?? null,
        ]);

        NotificationService::send(
            $earlyCheckout->student_id,
            'Izin Pulang Awal Disetujui',
            'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . ' telah disetujui.',
            'success',
        );

        return response()->json(['message' => 'Pengajuan disetujui.']);
    }

    public function rejectEarlyCheckout(Request $request, EarlyCheckoutRequest $earlyCheckout): JsonResponse
    {
        if (! $earlyCheckout->isPending()) {
            return response()->json(['message' => 'Pengajuan ini sudah diproses.'], 403);
        }

        $data = $request->validate(['reviewer_note' => 'required|string|max:255']);

        $earlyCheckout->update([
            'status'        => 'rejected',
            'reviewed_by'   => Auth::id(),
            'reviewed_at'   => now(),
            'reviewer_note' => $data['reviewer_note'],
        ]);

        NotificationService::send(
            $earlyCheckout->student_id,
            'Izin Pulang Awal Ditolak',
            'Pengajuan pulang lebih awal tanggal ' . $earlyCheckout->date->isoFormat('D MMMM Y') . ' ditolak. Alasan: ' . $data['reviewer_note'],
            'warning',
        );

        return response()->json(['message' => 'Pengajuan ditolak.']);
    }

    // ─── Conduct / Pelanggaran ────────────────────────────────────────────────

    public function conduct(Request $request): JsonResponse
    {
        $request->validate(['class_id' => 'required|exists:classes,id']);

        $classId = (int) $request->input('class_id');

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->withCount([
                'conductLogs as prestasi_count'    => fn($q) => $q->whereHas('category', fn($c) => $c->where('type', 'prestasi')),
                'conductLogs as pelanggaran_count' => fn($q) => $q->whereHas('category', fn($c) => $c->where('type', 'pelanggaran')),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'               => $s->id,
                'name'             => $s->name,
                'nis'              => $s->nis,
                'prestasi_count'   => $s->prestasi_count,
                'pelanggaran_count' => $s->pelanggaran_count,
            ]);

        return response()->json(['students' => $students]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function permitPayload(Permit $p): array
    {
        return [
            'id'             => $p->id,
            'student_name'   => $p->student?->name ?? '—',
            'class_name'     => $p->student?->schoolClass?->name ?? '—',
            'type'           => $p->type,
            'type_label'     => $p->typeLabel(),
            'start_date'     => $p->start_date?->toDateString(),
            'end_date'       => $p->end_date?->toDateString(),
            'reason'         => $p->reason,
            'status'         => $p->status,
            'rejection_note' => $p->rejection_note,
            'file_url'       => $p->file ? Storage::disk('public')->url($p->file) : null,
        ];
    }

    private function authorizePermitAction(Permit $permit): void
    {
        if (! $permit->isPending()) abort(403, 'Pengajuan ini sudah diproses.');
        $guru = Auth::user();
        if ($guru->role === 'admin' || $guru->isBk()) return;
        $homeroomClass = $guru->homeroomClass;
        if (! $homeroomClass) abort(403, 'Anda tidak berwenang menyetujui izin ini.');
        if ($permit->student->class_id !== $homeroomClass->id) abort(403, 'Siswa bukan anggota kelas wali Anda.');
    }

    private function authorizeForgotAttendance(ForgotAttendanceRequest $req): void
    {
        if (! $req->isPending()) abort(403, 'Pengajuan ini sudah diproses.');
        $guru = Auth::user();
        if ($guru->role === 'admin' || $guru->isBk()) return;
        $homeroomClass = $guru->homeroomClass;
        if (! $homeroomClass) abort(403, 'Anda tidak berwenang.');
        if ($req->student->class_id !== $homeroomClass->id) abort(403, 'Siswa bukan anggota kelas wali Anda.');
    }

    private function syncPermitAttendance(Permit $permit): void
    {
        $current = $permit->start_date->copy();
        while ($current->lte($permit->end_date)) {
            if ($current->isWeekday()) {
                Attendance::updateOrCreate(
                    ['user_id' => $permit->student_id, 'date' => $current->toDateString()],
                    ['status' => $permit->type, 'check_in_time' => null]
                );
            }
            $current->addDay();
        }
    }
}
