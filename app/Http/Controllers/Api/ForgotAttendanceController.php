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
                'type'         => $r->type ?? 'masuk',
                'type_label'   => $r->typeLabel(),
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
            'type'   => 'nullable|string|in:masuk,pulang,keduanya',
            'date'   => [
                'required', 'date',
                'before_or_equal:today',
                'after_or_equal:' . now()->subDays(30)->toDateString(),
            ],
            'reason' => 'required|string|max:500',
        ]);

        /** @var \App\Models\User $student */
        $student = Auth::user()->load('schoolClass.homeroomTeacher');

        $targetType = $data['type'] ?? 'masuk';

        $existingQuery = ForgotAttendanceRequest::where('student_id', $student->id)
            ->where('date', $data['date'])
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
            return response()->json([
                'message' => "Sudah ada pengajuan lupa absen {$typeLabel} untuk tanggal ini.",
            ], 422);
        }

        $attendance = Attendance::where('user_id', $student->id)
            ->whereDate('date', $data['date'])
            ->first();

        if ($attendance && $attendance->status !== 'alpa') {
            if ($targetType === 'masuk' && $attendance->check_in_time) {
                return response()->json([
                    'message' => 'Presensi masuk/datang untuk tanggal ini sudah tercatat.',
                ], 422);
            }
            if ($targetType === 'pulang' && $attendance->check_out_time) {
                return response()->json([
                    'message' => 'Presensi pulang untuk tanggal ini sudah tercatat.',
                ], 422);
            }
            if ($targetType === 'keduanya' && $attendance->check_in_time && $attendance->check_out_time) {
                return response()->json([
                    'message' => 'Presensi datang & pulang untuk tanggal ini sudah tercatat lengkap.',
                ], 422);
            }
        }

        $r = ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'type'       => $data['type'] ?? 'masuk',
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
