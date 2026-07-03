<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeroomConsultation;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeroomConsultationController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $consultations = HomeroomConsultation::with('teacher')
            ->where('student_id', $siswa->id)
            ->latest()
            ->get();

        $teacher = $siswa->schoolClass?->homeroomTeacher;

        return response()->json([
            'homeroom_teacher' => $teacher
                ? ['id' => $teacher->id, 'name' => $teacher->name]
                : null,
            'consultations' => $consultations->map(fn ($c) => $this->format($c))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $class = SchoolClass::find($siswa->class_id);
        if (! $class?->homeroom_teacher_id) {
            return response()->json(['message' => 'Kelas Anda belum memiliki wali kelas.'], 422);
        }

        $hasActive = HomeroomConsultation::where('student_id', $siswa->id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->exists();

        if ($hasActive) {
            return response()->json(
                ['message' => 'Anda masih memiliki pengajuan bimbingan yang belum selesai.'],
                422
            );
        }

        $data = $request->validate([
            'topic'        => 'required|string|max:200',
            'student_note' => 'nullable|string|max:1000',
        ]);

        $consultation = HomeroomConsultation::create([
            'student_id'   => $siswa->id,
            'teacher_id'   => $class->homeroom_teacher_id,
            'class_id'     => $siswa->class_id,
            'topic'        => $data['topic'],
            'student_note' => $data['student_note'] ?? null,
        ]);
        $consultation->load('teacher');

        return response()->json([
            'message'      => 'Pengajuan bimbingan berhasil dikirim.',
            'consultation' => $this->format($consultation),
        ], 201);
    }

    public function cancel(int $id): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $consultation = HomeroomConsultation::findOrFail($id);

        if ($consultation->student_id !== $siswa->id || ! $consultation->isPending()) {
            return response()->json(['message' => 'Pengajuan tidak dapat dibatalkan.'], 403);
        }

        $consultation->update([
            'status'            => 'cancelled',
            'cancelled_reason'  => 'Dibatalkan oleh siswa',
        ]);

        return response()->json(['message' => 'Pengajuan bimbingan dibatalkan.']);
    }

    private function format(HomeroomConsultation $c): array
    {
        return [
            'id'               => $c->id,
            'topic'            => $c->topic,
            'student_note'     => $c->student_note,
            'status'           => $c->status,
            'status_label'     => $c->statusLabel(),
            'scheduled_date'   => $c->scheduled_date?->toDateString(),
            'conducted_date'   => $c->conducted_date?->toDateString(),
            'teacher_name'     => $c->teacher?->name,
            'teacher_note'     => $c->teacher_note,
            'follow_up'        => $c->follow_up,
            'cancelled_reason' => $c->cancelled_reason,
            'created_at'       => $c->created_at->toIso8601String(),
        ];
    }
}
