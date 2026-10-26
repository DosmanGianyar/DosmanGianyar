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

        $pendingReport = $user->conductLogs()
            ->where('is_self_reported', true)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $categories = ConductCategory::active()
            ->where('type', 'pelanggaran')
            ->where('name', 'not like', '__sistem__%')
            ->orderBy('name')
            ->get();

        return view('siswa.conduct.index', compact('logs', 'prestasiCount', 'pelanggaranCount', 'categories', 'pendingReport'));
    }

    public function storeSelfReport(Request $request): RedirectResponse
    {
        $student = Auth::user();

        // Cegah siswa mengirim pengajuan berkali-kali jika masih ada yang pending (belum diverifikasi guru)
        $hasPending = ConductLog::where('student_id', $student->id)
            ->where('is_self_reported', true)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return redirect()->route('siswa.conduct.index')
                ->with('error', 'Anda masih memiliki 1 laporan pembinaan mandiri yang belum diverifikasi oleh Guru Piket. Anda baru dapat melapor lagi setelah laporan sebelumnya diverifikasi.');
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

        ConductLog::create([
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

        return redirect()->route('siswa.conduct.index')
            ->with('success', 'Pengajuan pembinaan mandiri berhasil dikirim. Silakan tunjukkan pengajuan Anda kepada Guru Piket untuk verifikasi.');
    }
}
