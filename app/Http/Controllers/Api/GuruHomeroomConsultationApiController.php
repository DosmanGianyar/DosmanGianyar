<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeroomConsultation;
use App\Models\StudentHomeroomTeacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruHomeroomConsultationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = Auth::user();

        $studentCount = StudentHomeroomTeacher::where('teacher_id', $teacher->id)->count();

        $status = $request->input('status', '');

        $consultations = HomeroomConsultation::where('teacher_id', $teacher->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with('student:id,name,nis,photo')
            ->latest()
            ->get()
            ->map(fn($c) => $this->formatConsultation($c));

        $counts = HomeroomConsultation::where('teacher_id', $teacher->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'student_count' => $studentCount,
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
        $c = HomeroomConsultation::where('teacher_id', Auth::id())->findOrFail($id);
        abort_unless($c->isPending(), 422, 'Hanya pengajuan yang menunggu yang dapat dijadwalkan.');

        $request->validate(['scheduled_date' => 'required|date|after_or_equal:today']);

        $c->update(['status' => 'scheduled', 'scheduled_date' => $request->scheduled_date]);

        return response()->json([
            'message'      => 'Bimbingan berhasil dijadwalkan.',
            'consultation' => $this->formatConsultation($c->fresh('student')),
        ]);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $c = HomeroomConsultation::where('teacher_id', Auth::id())->findOrFail($id);
        abort_unless($c->isScheduled() || $c->isPending(), 422, 'Hanya bimbingan yang terjadwal/menunggu yang dapat diselesaikan.');

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
            'message'      => 'Jurnal bimbingan berhasil disimpan.',
            'consultation' => $this->formatConsultation($c->fresh('student')),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $c = HomeroomConsultation::where('teacher_id', Auth::id())->findOrFail($id);
        abort_unless(in_array($c->status, ['pending', 'scheduled']), 422, 'Tidak dapat membatalkan bimbingan dengan status ini.');

        $request->validate(['cancelled_reason' => 'nullable|string|max:300']);

        $c->update([
            'status'           => 'cancelled',
            'cancelled_reason' => $request->cancelled_reason ?: 'Dibatalkan oleh Guru Wali',
        ]);

        return response()->json([
            'message'      => 'Pengajuan bimbingan dibatalkan.',
            'consultation' => $this->formatConsultation($c->fresh('student')),
        ]);
    }

    private function formatConsultation(HomeroomConsultation $c): array
    {
        return [
            'id'               => $c->id,
            'student_id'       => $c->student_id,
            'student_name'     => $c->student?->name ?? '—',
            'student_nis'      => $c->student?->nis,
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
