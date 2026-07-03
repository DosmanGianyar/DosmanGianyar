<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ForgotAttendanceRequest;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForgotAttendanceController extends Controller
{
    public function index(): JsonResponse
    {
        $requests = ForgotAttendanceRequest::where('student_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'date'         => $r->date->toDateString(),
                'reason'       => $r->reason,
                'status'       => $r->status,
                'status_label' => $this->statusLabel($r->status),
                'teacher_note' => $r->teacher_note,
                'created_at'   => $r->created_at->toIso8601String(),
            ]);

        return response()->json(['requests' => $requests]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date'   => [
                'required', 'date',
                'before:today',
                'after_or_equal:' . now()->subDays(30)->toDateString(),
            ],
            'reason' => 'required|string|max:500',
        ]);

        /** @var \App\Models\User $student */
        $student = Auth::user()->load('schoolClass.homeroomTeacher');

        $existing = ForgotAttendanceRequest::where('student_id', $student->id)
            ->where('date', $data['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Sudah ada pengajuan lupa absen untuk tanggal ini.',
            ], 422);
        }

        $attendance = Attendance::where('user_id', $student->id)
            ->whereDate('date', $data['date'])
            ->first();

        if ($attendance && $attendance->status !== 'alpa') {
            return response()->json([
                'message' => 'Presensi tanggal ini sudah tercatat sebagai ' . ucfirst($attendance->status) . '.',
            ], 422);
        }

        $r = ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'date'       => $data['date'],
            'reason'     => $data['reason'],
            'status'     => 'pending',
        ]);

        $homeroomTeacher = $student->schoolClass?->homeroomTeacher;
        if ($homeroomTeacher) {
            NotificationService::send(
                userId: $homeroomTeacher->id,
                title:  'Pengajuan Lupa Absen',
                body:   $student->name . ' mengajukan lupa absen pada ' . Carbon::parse($data['date'])->isoFormat('D MMMM Y'),
                type:   'info',
                url:    null,
            );
        }

        return response()->json([
            'message' => 'Pengajuan lupa absen berhasil dikirim. Menunggu persetujuan wali kelas.',
            'request' => [
                'id'           => $r->id,
                'date'         => $r->date->toDateString(),
                'reason'       => $r->reason,
                'status'       => $r->status,
                'status_label' => $this->statusLabel($r->status),
                'created_at'   => $r->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $r = ForgotAttendanceRequest::where('student_id', Auth::id())->findOrFail($id);

        if (! $r->isPending()) {
            return response()->json([
                'message' => 'Tidak dapat membatalkan pengajuan yang sudah diproses.',
            ], 422);
        }

        $r->delete();

        return response()->json(['message' => 'Pengajuan berhasil dibatalkan.']);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => ucfirst($status),
        };
    }
}
