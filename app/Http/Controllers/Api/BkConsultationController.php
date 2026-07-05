<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BkConsultation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BkConsultationController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $consultations = BkConsultation::with('teacher:id,name')
            ->where('student_id', $siswa->id)
            ->latest()
            ->get()
            ->map(fn($c) => $this->format($c));

        $bkTeachers = User::where('role', 'guru')
            ->where(fn($q) => $q
                ->whereRaw("LOWER(subject) LIKE '%bk%'")
                ->orWhereHas('subjects', fn($q2) => $q2->whereRaw("LOWER(name) LIKE '%bk%'"))
            )
            ->select('id', 'name', 'subject')
            ->orderBy('name')
            ->get();

        $activeConsultation = BkConsultation::where('student_id', $siswa->id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->with('teacher:id,name')
            ->first();

        return response()->json([
            'consultations'      => $consultations,
            'bk_teachers'        => $bkTeachers,
            'active_consultation' => $activeConsultation ? $this->format($activeConsultation) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $hasActive = BkConsultation::where('student_id', $siswa->id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->exists();

        if ($hasActive) {
            return response()->json(['message' => 'Anda masih memiliki pengajuan bimbingan BK yang belum selesai.'], 422);
        }

        $data = $request->validate([
            'teacher_id'   => 'required|exists:users,id',
            'topic'        => 'required|string|max:200',
            'student_note' => 'nullable|string|max:1000',
        ]);

        $teacher = User::findOrFail($data['teacher_id']);
        if (! $teacher->isBk()) {
            return response()->json(['message' => 'Guru yang dipilih bukan Guru BK.'], 422);
        }

        $consultation = BkConsultation::create([
            'student_id'   => $siswa->id,
            'teacher_id'   => $data['teacher_id'],
            'topic'        => $data['topic'],
            'student_note' => $data['student_note'] ?? null,
        ]);
        $consultation->load('teacher:id,name');

        return response()->json([
            'message'      => 'Pengajuan bimbingan BK berhasil dikirim.',
            'consultation' => $this->format($consultation),
        ], 201);
    }

    public function changeTeacher(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $c     = BkConsultation::where('student_id', $siswa->id)->findOrFail($id);

        if (! $c->isPending()) {
            return response()->json(['message' => 'Hanya pengajuan yang belum ditanggapi yang dapat diganti guru BK-nya.'], 422);
        }

        $data    = $request->validate(['teacher_id' => 'required|exists:users,id']);
        $teacher = User::findOrFail($data['teacher_id']);
        if (! $teacher->isBk()) {
            return response()->json(['message' => 'Guru yang dipilih bukan Guru BK.'], 422);
        }

        $c->update(['teacher_id' => $data['teacher_id']]);

        return response()->json([
            'message'      => 'Guru BK berhasil diganti.',
            'consultation' => $this->format($c->fresh('teacher')),
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $c     = BkConsultation::where('student_id', $siswa->id)->findOrFail($id);

        if (! $c->isPending()) {
            return response()->json(['message' => 'Hanya pengajuan yang menunggu yang dapat dibatalkan.'], 422);
        }

        $c->update(['status' => 'cancelled', 'cancelled_reason' => 'Dibatalkan oleh siswa']);

        return response()->json(['message' => 'Pengajuan bimbingan BK dibatalkan.']);
    }

    private function format(BkConsultation $c): array
    {
        return [
            'id'               => $c->id,
            'topic'            => $c->topic,
            'student_note'     => $c->student_note,
            'status'           => $c->status,
            'status_label'     => $c->statusLabel(),
            'teacher_id'       => $c->teacher_id,
            'teacher_name'     => $c->teacher?->name,
            'scheduled_date'   => $c->scheduled_date?->toDateString(),
            'conducted_date'   => $c->conducted_date?->toDateString(),
            'teacher_note'     => $c->teacher_note,
            'follow_up'        => $c->follow_up,
            'cancelled_reason' => $c->cancelled_reason,
            'created_at'       => $c->created_at->toIso8601String(),
        ];
    }
}
