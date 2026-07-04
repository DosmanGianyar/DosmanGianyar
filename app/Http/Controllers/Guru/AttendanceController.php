<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EarlyCheckoutRequest;
use App\Models\Permit;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $guru = Auth::user();

        $classes = SchoolClass::with('students')->get();
        $selectedClassId = $request->input('class_id', $guru->homeroomClass?->id ?? $classes->first()?->id);
        $date = $request->input('date', today()->toDateString());

        $students = User::where('role', 'siswa')
            ->where('class_id', $selectedClassId)
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

        $summary = [
            'hadir'      => 0,
            'terlambat'  => 0,
            'izin'       => 0,
            'sakit'      => 0,
            'alpa'       => 0,
            'dispensasi' => 0,
        ];

        $effectiveStatuses = [];
        foreach ($students as $student) {
            $att = $student->attendances->first();
            $hasEarlyApproval = isset($approvedEarlyCheckouts[$student->id]);
            $status = $att ? $att->effectiveStatus($hasEarlyApproval) : 'alpa';
            $effectiveStatuses[$student->id] = $status;
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        return view('guru.attendance.index', compact(
            'classes', 'selectedClassId', 'date', 'students', 'summary', 'effectiveStatuses'
        ));
    }

    public function rekap(Request $request): View
    {
        /** @var \App\Models\User $guru */
        $guru = Auth::user();

        $classes         = SchoolClass::orderBy('name')->get();
        $selectedClassId = $request->input('class_id', $guru->homeroomClass?->id ?? $classes->first()?->id);

        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);
        $month = max(1, min(12, $month));
        $year  = max(2020, min(now()->year, $year));

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Weekdays only (Mon–Fri)
        $schoolDays = collect();
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if ($cursor->isWeekday()) {
                $schoolDays->push($cursor->copy());
            }
            $cursor->addDay();
        }

        $students = User::where('role', 'siswa')
            ->where('class_id', $selectedClassId)
            ->orderBy('name')
            ->get();

        // Fetch all attendance records for the month in one query
        $attendances = Attendance::whereHas('student', fn($q) => $q->where('class_id', $selectedClassId))
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        // Load approved early checkouts for all students for the month
        $approvedEarlyCheckouts = EarlyCheckoutRequest::whereIn('student_id', $students->pluck('id'))
            ->whereBetween('date', [$start, $end])
            ->where('status', 'approved')
            ->get(['student_id', 'date'])
            ->groupBy('student_id')
            ->map(fn($g) => $g->mapWithKeys(fn($r) => [$r->date->format('Y-m-d') => true])->all());

        // Per-student summary
        $studentData = $students->map(function ($student) use ($attendances, $schoolDays, $approvedEarlyCheckouts) {
            $recs             = $attendances->get($student->id, collect())->keyBy(fn($a) => $a->date->format('Y-m-d'));
            $studentApprovals = $approvedEarlyCheckouts->get($student->id, []);
            $counts           = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'dispensasi' => 0];
            $effectiveStatuses = [];
            foreach ($schoolDays as $day) {
                $dateStr          = $day->format('Y-m-d');
                $att              = $recs->get($dateStr);
                $hasEarlyApproval = isset($studentApprovals[$dateStr]);
                $status           = $att ? $att->effectiveStatus($hasEarlyApproval) : 'alpa';
                $effectiveStatuses[$dateStr] = $status;
                if (isset($counts[$status])) $counts[$status]++;
            }
            return [
                'student'            => $student,
                'records'            => $recs,
                'effective_statuses' => $effectiveStatuses,
                'counts'             => $counts,
            ];
        });

        $prevMonth = $start->copy()->subMonth();
        $nextMonth = $start->copy()->addMonth();
        $canNext   = $nextMonth->lte(now()->endOfMonth());

        return view('guru.attendance.rekap', compact(
            'classes', 'selectedClassId', 'schoolDays',
            'studentData', 'month', 'year', 'start',
            'prevMonth', 'nextMonth', 'canNext'
        ));
    }

    public function manual(Request $request): RedirectResponse
    {
        $guru = Auth::user();
        if (! $guru->homeroomClass && ! $guru->isBk() && $guru->role !== 'admin') {
            abort(403, 'Anda tidak berwenang mengubah absensi.');
        }

        $request->validate([
            'student_id' => 'required|exists:users,id',
            'date'       => 'required|date|before_or_equal:today',
            'status'     => ['required', Rule::in(['hadir','terlambat','izin','sakit','alpa','dispensasi'])],
        ]);

        Attendance::updateOrCreate(
            [
                'user_id' => $request->student_id,
                'date'    => $request->date,
            ],
            [
                'status'        => $request->status,
                'check_in_time' => in_array($request->status, ['hadir','terlambat']) ? now()->toTimeString() : null,
            ]
        );

        return back()->with('success', 'Absensi berhasil diperbarui.');
    }

    public function permits(Request $request): View
    {
        $guru   = Auth::user();
        $status = $request->input('status', 'pending');

        $permits = Permit::with('student.schoolClass')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when(
                ! $guru->isBk() && $guru->role !== 'admin' && $guru->homeroomClass,
                fn($q) => $q->whereHas('student', fn($s) => $s->where('class_id', $guru->homeroomClass->id))
            )
            ->when(
                ! $guru->isBk() && $guru->role !== 'admin' && ! $guru->homeroomClass,
                fn($q) => $q->whereIn('status', ['approved', 'rejected'])
            )
            ->latest()
            ->paginate(15);

        return view('guru.attendance.permits', compact('permits', 'status'));
    }

    public function approvePermit(Permit $permit): RedirectResponse
    {
        $this->authorizePermitAction($permit);

        $permit->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
        ]);

        $this->syncPermitAttendance($permit, 'approved');

        NotificationService::send(
            $permit->student_id,
            "{$permit->typeLabel()} Disetujui",
            "Pengajuan {$permit->typeLabel()} kamu untuk tanggal {$permit->start_date->isoFormat('D MMM Y')} telah disetujui.",
            'success',
            route('siswa.permit.index'),
        );

        return back()->with('success', $permit->typeLabel() . ' disetujui.');
    }

    public function rejectPermit(Request $request, Permit $permit): RedirectResponse
    {
        $this->authorizePermitAction($permit);

        $request->validate([
            'rejection_note' => 'required|string|max:255',
        ]);

        $permit->update([
            'status'          => 'rejected',
            'approved_by'     => Auth::id(),
            'rejection_note'  => $request->rejection_note,
        ]);

        NotificationService::send(
            $permit->student_id,
            "{$permit->typeLabel()} Ditolak",
            "Pengajuan {$permit->typeLabel()} kamu ditolak. Alasan: {$request->rejection_note}",
            'warning',
            route('siswa.permit.index'),
        );

        return back()->with('success', $permit->typeLabel() . ' ditolak.');
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

    private function syncPermitAttendance(Permit $permit, string $action): void
    {
        if ($action !== 'approved') {
            return;
        }

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
