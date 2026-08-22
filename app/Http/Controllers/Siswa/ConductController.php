<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Services\ImageService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConductController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $logs = $user->conductLogs()
            ->with(['category', 'verifier'])
            ->latest()
            ->paginate(20);

        $allLogs = $user->conductLogs()->with('category')->get();
        $prestasiCount    = $allLogs->filter(fn ($l) => $l->isPrestasi() && $l->status === 'verified')->count();
        $pelanggaranCount = $allLogs->filter(fn ($l) => $l->isPelanggaran() && $l->status === 'verified')->count();

        $categories = ConductCategory::active()
            ->where('type', 'pelanggaran')
            ->where('name', 'not like', '__sistem__%')
            ->orderBy('name')
            ->get();

        return view('siswa.conduct.index', compact('logs', 'prestasiCount', 'pelanggaranCount', 'categories'));
    }

    public function storeSelfReport(Request $request): RedirectResponse
    {
        $request->validate([
            'reason'      => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'photo'       => 'nullable|image|max:2048',
        ]);

        $student = Auth::user();

        $category = ConductCategory::firstOrCreate(
            ['name' => 'Terlambat Masuk Sekolah'],
            ['type' => 'pelanggaran', 'context' => 'sidak', 'is_active' => true]
        );

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = ImageService::store($request->file('photo'), 'conduct', maxWidth: 1280, quality: 80);
        }

        $reason = trim($request->input('reason', 'Terlambat Masuk Sekolah'));
        $desc   = trim((string) $request->input('description', ''));
        $fullNote = "[Pengajuan Mandiri: {$reason}]" . ($desc ? " {$desc}" : '');

        ConductLog::create([
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

        return redirect()->route('siswa.conduct.index')
            ->with('success', 'Pengajuan pembinaan mandiri berhasil dikirim. Silakan tunjukkan pengajuan Anda kepada Guru Piket untuk verifikasi.');
    }
}
