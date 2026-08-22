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
        $request->validate([
            'reason'      => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'photo'       => 'nullable|image|max:2048',
        ]);

        /** @var \App\Models\User $student */
        $student = Auth::user();

        $category = ConductCategory::firstOrCreate(
            ['name' => 'Terlambat Masuk Sekolah'],
            ['type' => 'pelanggaran', 'context' => 'sidak', 'is_active' => true]
        );

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = ImageService::store($request->file('photo'), 'conduct', maxWidth: 1280, quality: 80);
        }

        $reason   = trim($request->input('reason', 'Terlambat Masuk Sekolah'));
        $desc     = trim((string) $request->input('description', ''));
        $fullNote = "[Pengajuan Mandiri: {$reason}]" . ($desc ? " {$desc}" : '');

        $log = ConductLog::create([
            'student_id'       => $student->id,
            'teacher_id'       => null,
            'category_id'      => $category->id,
            'photo'            => $photoPath,
            'note'             => $fullNote,
            'description'      => "Pengajuan Mandiri Siswa: {$reason}" . ($desc ? " - {$desc}" : ''),
            'severity'         => 'ringan',
            'type'             => 'pelanggaran',
            'is_self_reported' => true,
            'status'           => 'pending',
        ]);

        NotificationService::notifyHomeroomTeacher(
            $student,
            "Pengajuan Pembinaan Mandiri",
            "Siswa {$student->name} ({$student->schoolClass?->name}) mengajukan pembinaan: {$reason}.",
            'warning'
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pembinaan mandiri berhasil dikirim.',
            'data'    => $log,
        ]);
    }
}
