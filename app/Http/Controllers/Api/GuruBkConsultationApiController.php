<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BkConsultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruBkConsultationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = Auth::user();
        abort_unless($teacher->isBk(), 403, 'Hanya Guru BK yang dapat mengakses halaman ini.');

        $status = $request->input('status', '');

        $consultations = BkConsultation::where('teacher_id', $teacher->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with('student:id,name,nis,photo,class_id')
            ->with('student.schoolClass:id,name')
            ->latest()
            ->get()
            ->map(fn($c) => $this->format($c));

        $counts = BkConsultation::where('teacher_id', $teacher->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'consultations' => $consultations,
            'counts'        => [
                'pending'   => $counts['pending']   ?? 0,
                'scheduled' => $counts['scheduled'] ?? 0,
                'completed' => $counts['completed'] ?? 0,
                'cancelled' => $counts['cancelled'] ?? 0,
            ],
        ]);
    }

    public function schedule(Request $request, int $id): JsonResponse
    {
        $c = BkConsultation::where('teacher_id', Auth::id())->findOrFail($id);
        abort_unless($c->isPending(), 422, 'Hanya pengajuan yang menunggu yang dapat dijadwalkan.');

        $request->validate(['scheduled_date' => 'required|date|after_or_equal:today']);

        $c->update(['status' => 'scheduled', 'scheduled_date' => $request->scheduled_date]);

        return response()->json([
            'message'      => 'Bimbingan berhasil dijadwalkan.',
            'consultation' => $this->format($c->fresh('student')),
        ]);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $c = BkConsultation::where('teacher_id', Auth::id())->findOrFail($id);
        abort_unless($c->isScheduled() || $c->isPending(), 422);

        $request->validate([
            'conducted_date' => 'required|date',
            'teacher_note'   => 'required|string|max:2000',
            'follow_up'      => 'nullable|string|max:1000',
        ]);

        $c->update([
            'status'         => 'completed',
            'conducted_date' => $request->conducted_date,
            'teacher_note'   => $request->teacher_note,
            'follow_up'      => $request->follow_up,
        ]);

        return response()->json([
            'message'      => 'Jurnal bimbingan BK berhasil disimpan.',
            'consultation' => $this->format($c->fresh('student')),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $c = BkConsultation::where('teacher_id', Auth::id())->findOrFail($id);
        abort_unless(in_array($c->status, ['pending', 'scheduled']), 422);

        $request->validate(['cancelled_reason' => 'nullable|string|max:300']);

        $c->update([
            'status'           => 'cancelled',
            'cancelled_reason' => $request->cancelled_reason ?: 'Dibatalkan oleh Guru BK',
        ]);

        return response()->json([
            'message'      => 'Pengajuan bimbingan dibatalkan.',
            'consultation' => $this->format($c->fresh('student')),
        ]);
    }

    private function format(BkConsultation $c): array
    {
        return [
            'id'               => $c->id,
            'student_id'       => $c->student_id,
            'student_name'     => $c->student?->name ?? '—',
            'student_nis'      => $c->student?->nis,
            'student_class'    => $c->student?->schoolClass?->name,
            'topic'            => $c->topic,
            'student_note'     => $c->student_note,
            'status'           => $c->status,
            'status_label'     => $c->statusLabel(),
            'scheduled_date'   => $c->scheduled_date?->toDateString(),
            'conducted_date'   => $c->conducted_date?->toDateString(),
            'teacher_note'     => $c->teacher_note,
            'follow_up'        => $c->follow_up,
            'cancelled_reason' => $c->cancelled_reason,
            'created_at'       => $c->created_at->toDateString(),
        ];
    }
}
