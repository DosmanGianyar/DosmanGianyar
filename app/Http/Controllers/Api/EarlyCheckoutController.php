<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EarlyCheckoutRequest;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarlyCheckoutController extends Controller
{
    public function index(): JsonResponse
    {
        $requests = EarlyCheckoutRequest::where('student_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'date'           => $r->date->toDateString(),
                'requested_time' => substr((string) $r->requested_time, 0, 5),
                'reason'         => $r->reason,
                'status'         => $r->status,
                'status_label'   => $this->statusLabel($r->status),
                'reviewer_note'  => $r->reviewer_note,
                'created_at'     => $r->created_at->toIso8601String(),
            ]);

        return response()->json(['requests' => $requests]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date'           => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(7)->toDateString(),
            'requested_time' => 'required|date_format:H:i',
            'reason'         => 'required|string|max:500',
        ]);

        /** @var \App\Models\User $student */
        $student = Auth::user();

        $existing = EarlyCheckoutRequest::where('student_id', $student->id)
            ->where('date', $data['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Sudah ada pengajuan izin pulang untuk tanggal ini.',
            ], 422);
        }

        $r = EarlyCheckoutRequest::create([
            'student_id'     => $student->id,
            'date'           => $data['date'],
            'requested_time' => $data['requested_time'] . ':00',
            'reason'         => $data['reason'],
            'status'         => 'pending',
        ]);

        NotificationService::broadcastToRole(
            roles: ['guru', 'admin'],
            title: 'Izin Pulang Lebih Awal',
            body:  $student->name . ' mengajukan izin pulang lebih awal pada ' .
                   Carbon::parse($data['date'])->isoFormat('D MMMM Y') .
                   ' pukul ' . $data['requested_time'],
            type:  'info',
            url:   null,
        );

        return response()->json([
            'message' => 'Pengajuan izin pulang lebih awal berhasil dikirim. Menunggu persetujuan guru.',
            'request' => [
                'id'             => $r->id,
                'date'           => $r->date->toDateString(),
                'requested_time' => $data['requested_time'],
                'reason'         => $r->reason,
                'status'         => $r->status,
                'status_label'   => $this->statusLabel($r->status),
                'created_at'     => $r->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $r = EarlyCheckoutRequest::where('student_id', Auth::id())->findOrFail($id);

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
