<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Services\ImageService;
use App\Services\NotificationService;
use App\Services\StudentDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConductController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        return response()->json(StudentDataService::conductLogs($siswa));
    }

    public function storeSelfReport(Request $request): JsonResponse
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        // Cegah siswa mengirim pengajuan berkali-kali jika masih ada yang pending (belum diverifikasi guru)
        $hasPending = ConductLog::where('student_id', $student->id)
            ->where('is_self_reported', true)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki 1 pengajuan pembinaan mandiri yang sedang menunggu verifikasi Guru Piket. Anda baru dapat melapor lagi setelah pengajuan tersebut diverifikasi.',
            ], 422);
        }

        $hasRejected = ConductLog::where('student_id', $student->id)
            ->where('is_self_reported', true)
            ->where('status', 'rejected')
            ->exists();

        if ($hasRejected) {
            return response()->json([
                'success' => false,
                'message' => 'Anda memiliki laporan pembinaan yang dikembalikan oleh guru. Silakan perbaiki alasan laporan tersebut atau batalkan pengajuan sebelumnya terlebih dahulu.',
            ], 422);
        }

        $request->validate([
            'reason'       => 'required|string|max:100',
            'other_reason' => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:500',
            'photo'        => 'nullable|image|max:2048',
        ]);

        $reason      = trim($request->input('reason', 'Terlambat Masuk Sekolah'));
        $otherReason = trim((string) $request->input('other_reason', ''));
        $desc        = trim((string) $request->input('description', ''));

        if ($reason === 'Lainnya') {
            $displayReason = $otherReason !== '' ? $otherReason : 'Pelanggaran Lainnya';
            $category = ConductCategory::firstOrCreate(
                ['name' => '__sistem__pelanggaran_lainnya'],
                ['type' => 'pelanggaran', 'context' => 'sidak', 'is_active' => true]
            );
        } else {
            $displayReason = $reason;
            $context = match ($reason) {
                'Terlambat Masuk Sekolah'           => 'sidak',
                'Terlambat Kembali dari Izin Keluar' => 'izin',
                'Seragam / Atribut Tidak Lengkap'    => 'sidak',
                default                             => 'sidak',
            };
            $category = ConductCategory::firstOrCreate(
                ['name' => $reason],
                ['type' => 'pelanggaran', 'context' => $context, 'is_active' => true]
            );
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = ImageService::store($request->file('photo'), 'conduct', maxWidth: 1280, quality: 80);
        }

        $fullNote = "[{$displayReason}]" . ($desc ? " {$desc}" : '');

        $log = ConductLog::create([
            'student_id'       => $student->id,
            'teacher_id'       => null,
            'category_id'      => $category->id,
            'photo'            => $photoPath,
            'note'             => $fullNote,
            'description'      => "Pengajuan Mandiri Siswa: {$displayReason}" . ($desc ? " - {$desc}" : ''),
            'severity'         => 'ringan',
            'type'             => 'pelanggaran',
            'is_self_reported' => true,
            'status'           => 'pending',
        ]);

        NotificationService::notifyHomeroomTeacher(
            $student,
            "Pengajuan Pembinaan Mandiri",
            "Siswa {$student->name} ({$student->schoolClass?->name}) mengajukan pembinaan: {$displayReason}.",
            'conduct',
            route('guru.conduct.today')
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pembinaan mandiri berhasil dikirim.',
            'data'    => $log,
        ]);
    }

    public function updateSelfReport(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        $log = ConductLog::where('id', $id)
            ->where('student_id', $student->id)
            ->where('is_self_reported', true)
            ->firstOrFail();

        if ($log->status !== 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan dengan status dikembalikan yang dapat diperbaiki.',
            ], 422);
        }

        $request->validate([
            'reason'       => 'required|string|max:100',
            'other_reason' => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:500',
            'photo'        => 'nullable|image|max:2048',
        ]);

        $reason      = trim($request->input('reason', 'Terlambat Masuk Sekolah'));
        $otherReason = trim((string) $request->input('other_reason', ''));
        $desc        = trim((string) $request->input('description', ''));

        if ($reason === 'Lainnya') {
            $displayReason = $otherReason !== '' ? $otherReason : 'Pelanggaran Lainnya';
            $category = ConductCategory::firstOrCreate(
                ['name' => '__sistem__pelanggaran_lainnya'],
                ['type' => 'pelanggaran', 'context' => 'sidak', 'is_active' => true]
            );
        } else {
            $displayReason = $reason;
            $context = match ($reason) {
                'Terlambat Masuk Sekolah'           => 'sidak',
                'Terlambat Kembali dari Izin Keluar' => 'izin',
                'Seragam / Atribut Tidak Lengkap'    => 'sidak',
                default                             => 'sidak',
            };
            $category = ConductCategory::firstOrCreate(
                ['name' => $reason],
                ['type' => 'pelanggaran', 'context' => $context, 'is_active' => true]
            );
        }

        $photoPath = $log->photo;
        if ($request->hasFile('photo')) {
            if ($log->photo) {
                Storage::disk('public')->delete($log->photo);
            }
            $photoPath = ImageService::store($request->file('photo'), 'conduct', maxWidth: 1280, quality: 80);
        }

        $fullNote = "[{$displayReason}]" . ($desc ? " {$desc}" : '');

        $log->update([
            'category_id'    => $category->id,
            'photo'          => $photoPath,
            'note'           => $fullNote,
            'description'    => "Pengajuan Mandiri Siswa: {$displayReason}" . ($desc ? " - {$desc}" : ''),
            'status'         => 'pending',
            'rejection_note' => null,
            'verifier_id'    => null,
            'verified_at'    => null,
        ]);

        NotificationService::notifyHomeroomTeacher(
            $student,
            "Perbaikan Pengajuan Pembinaan Mandiri",
            "Siswa {$student->name} ({$student->schoolClass?->name}) telah memperbaiki alasan pengajuan pembinaan: {$displayReason}.",
            'conduct',
            route('guru.conduct.today')
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pembinaan berhasil diperbaiki dan dikirim ulang untuk verifikasi Guru Piket.',
            'data'    => $log,
        ]);
    }

    public function destroySelfReport(int $id): JsonResponse
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        $log = ConductLog::where('id', $id)
            ->where('student_id', $student->id)
            ->where('is_self_reported', true)
            ->firstOrFail();

        if (!in_array($log->status, ['pending', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diverifikasi tidak dapat dibatalkan.',
            ], 422);
        }

        if ($log->photo) {
            Storage::disk('public')->delete($log->photo);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pembinaan mandiri berhasil dibatalkan / dihapus.',
        ]);
    }
}
