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
            'warning'
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pembinaan mandiri berhasil dikirim.',
            'data'    => $log,
        ]);
    }
}
