<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\User;
use App\Services\ExtracurricularImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(): View
    {
        $extracurriculars = Extracurricular::with(['teachers', 'students'])->orderBy('name')->get();

        return view('admin.extracurriculars.index', [
            'extracurriculars' => $extracurriculars,
        ]);
    }

    public function importForm(): View
    {
        $defaultFileExists = file_exists(public_path('ekstra.csv'));

        return view('admin.extracurriculars.import', [
            'defaultFileExists' => $defaultFileExists,
        ]);
    }

    public function preview(Request $request): View
    {
        $filePath = null;

        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:4096',
            ]);
            $filePath = $request->file('file')->getRealPath();
        } else {
            $filePath = public_path('ekstra.csv');
        }

        if (! file_exists($filePath)) {
            return redirect()->route('admin.extracurriculars.import')
                ->with('error', 'File CSV ekstra.csv tidak ditemukan.');
        }

        $previewData = ExtracurricularImportService::parseCsv($filePath);
        $allTeachers = User::where('role', 'guru')->orderBy('name')->get();
        $allStudents = User::where('role', 'siswa')->orderBy('name')->get();

        return view('admin.extracurriculars.preview', [
            'previewData' => $previewData,
            'allTeachers' => $allTeachers,
            'allStudents' => $allStudents,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'extracurriculars'                   => 'required|array',
            'extracurriculars.*.name'           => 'required|string|max:255',
            'extracurriculars.*.contact_person'  => 'nullable|string|max:255',
            'extracurriculars.*.teacher_ids'     => 'nullable|array',
            'extracurriculars.*.ketua_id'        => 'nullable|integer',
            'extracurriculars.*.wakil_ketua_id'  => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('extracurriculars', []) as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                $teacherIds = array_filter(array_map('intval', $data['teacher_ids'] ?? []));
                $firstTeacherId = $teacherIds[0] ?? null;

                $extra = Extracurricular::updateOrCreate(
                    ['name' => trim($data['name'])],
                    [
                        'contact_person' => $data['contact_person'] ?? null,
                        'pembina_id'     => $firstTeacherId,
                    ]
                );

                // Sync Teachers (Pembina)
                $extra->teachers()->sync($teacherIds);

                // Sync Students (Ketua & Wakil Ketua)
                $studentSync = [];
                if (! empty($data['ketua_id'])) {
                    $studentSync[$data['ketua_id']] = ['role' => 'ketua'];
                }
                if (! empty($data['wakil_ketua_id'])) {
                    $studentSync[$data['wakil_ketua_id']] = ['role' => 'wakil_ketua'];
                }
                $extra->students()->sync($studentSync);
            }
        });

        return redirect('/admin/extracurriculars')
            ->with('success', 'Data Ekstrakurikuler, Pembina, Ketua, dan Wakil Ketua berhasil disimpan!');
    }

    public function destroy(Extracurricular $extracurricular)
    {
        $extracurricular->delete();

        return redirect()->route('admin.extracurriculars.index')
            ->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    public function approvals(): View
    {
        $pendingMembers = \App\Models\ExtracurricularMember::whereIn('status', ['pending_join', 'pending_leave'])
            ->with(['user:id,name,class_id,nis', 'user.schoolClass:id,name', 'extracurricular:id,name'])
            ->latest()
            ->paginate(25);

        return view('admin.extracurriculars.approvals', compact('pendingMembers'));
    }

    public function approveMember(int $id)
    {
        $member = \App\Models\ExtracurricularMember::with(['extracurricular', 'user'])->findOrFail($id);

        if ($member->status === 'pending_join') {
            $member->update([
                'status'      => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            \Illuminate\Support\Facades\DB::table('extracurricular_students')->updateOrInsert(
                ['extracurricular_id' => $member->extracurricular_id, 'student_id' => $member->user_id],
                ['updated_at' => now(), 'created_at' => now()]
            );
            if ($member->user_id) {
                \App\Services\NotificationService::send(
                    $member->user_id,
                    'Pendaftaran Ekstra Disetujui! 🎉',
                    "Pendaftaran Anda di ekstrakurikuler {$member->extracurricular?->name} telah disetujui oleh Sekolah.",
                    'success'
                );
            }
        } else {
            $member->update([
                'status'      => 'inactive',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            \Illuminate\Support\Facades\DB::table('extracurricular_students')
                ->where('extracurricular_id', $member->extracurricular_id)
                ->where('student_id', $member->user_id)
                ->delete();

            if ($member->user_id) {
                \App\Services\NotificationService::send(
                    $member->user_id,
                    'Pengajuan Keluar Ekstra Disetujui',
                    "Pengajuan keluar dari ekstrakurikuler {$member->extracurricular?->name} telah disetujui.",
                    'info'
                );
            }
        }

        return back()->with('success', 'Pengajuan anggota ekstra berhasil disetujui.');
    }

    public function rejectMember(int $id)
    {
        $member = \App\Models\ExtracurricularMember::with(['extracurricular', 'user'])->findOrFail($id);

        if ($member->status === 'pending_join') {
            $member->update([
                'status'      => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            if ($member->user_id) {
                \App\Services\NotificationService::send(
                    $member->user_id,
                    'Pendaftaran Ekstra Ditolak',
                    "Mohon maaf, pendaftaran Anda di ekstrakurikuler {$member->extracurricular?->name} belum dapat disetujui.",
                    'danger'
                );
            }
        } else {
            $member->update(['status' => 'active']);
            if ($member->user_id) {
                \App\Services\NotificationService::send(
                    $member->user_id,
                    'Pengajuan Keluar Ekstra Ditolak',
                    "Pengajuan keluar dari ekstrakurikuler {$member->extracurricular?->name} tidak disetujui.",
                    'warning'
                );
            }
        }

        return back()->with('success', 'Pengajuan anggota ekstra berhasil ditolak.');
    }

    public function bulkApproveMember(Request $request)
    {
        $ids = $request->input('member_ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Tidak ada pengajuan yang dipilih.');
        }

        $members = \App\Models\ExtracurricularMember::with(['extracurricular', 'user'])->whereIn('id', $ids)->get();
        $count = 0;

        foreach ($members as $member) {
            if ($member->status === 'pending_join') {
                $member->update([
                    'status'      => 'active',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('extracurricular_students')->updateOrInsert(
                    ['extracurricular_id' => $member->extracurricular_id, 'student_id' => $member->user_id],
                    ['updated_at' => now(), 'created_at' => now()]
                );
                if ($member->user_id) {
                    \App\Services\NotificationService::send(
                        $member->user_id,
                        'Pendaftaran Ekstra Disetujui! 🎉',
                        "Pendaftaran Anda di ekstrakurikuler {$member->extracurricular?->name} telah disetujui oleh Sekolah.",
                        'success'
                    );
                }
                $count++;
            } elseif ($member->status === 'pending_leave') {
                $member->update([
                    'status'      => 'inactive',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('extracurricular_students')
                    ->where('extracurricular_id', $member->extracurricular_id)
                    ->where('student_id', $member->user_id)
                    ->delete();

                if ($member->user_id) {
                    \App\Services\NotificationService::send(
                        $member->user_id,
                        'Pengajuan Keluar Ekstra Disetujui',
                        "Pengajuan keluar dari ekstrakurikuler {$member->extracurricular?->name} telah disetujui.",
                        'info'
                    );
                }
                $count++;
            }
        }

        return back()->with('success', "Sebanyak {$count} pengajuan ekstra berhasil disetujui.");
    }

    public function bulkRejectMember(Request $request)
    {
        $ids = $request->input('member_ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Tidak ada pengajuan yang dipilih.');
        }

        $members = \App\Models\ExtracurricularMember::with(['extracurricular', 'user'])->whereIn('id', $ids)->get();
        $count = 0;

        foreach ($members as $member) {
            if ($member->status === 'pending_join') {
                $member->update([
                    'status'      => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
                if ($member->user_id) {
                    \App\Services\NotificationService::send(
                        $member->user_id,
                        'Pendaftaran Ekstra Ditolak',
                        "Mohon maaf, pendaftaran Anda di ekstrakurikuler {$member->extracurricular?->name} belum dapat disetujui.",
                        'danger'
                    );
                }
                $count++;
            } elseif ($member->status === 'pending_leave') {
                $member->update(['status' => 'active']);
                if ($member->user_id) {
                    \App\Services\NotificationService::send(
                        $member->user_id,
                        'Pengajuan Keluar Ekstra Ditolak',
                        "Pengajuan keluar dari ekstrakurikuler {$member->extracurricular?->name} tidak disetujui.",
                        'warning'
                    );
                }
                $count++;
            }
        }

        return back()->with('success', "Sebanyak {$count} pengajuan ekstra berhasil ditolak.");
    }

    public function cancelMember(int $id)
    {
        $member = \App\Models\ExtracurricularMember::with(['extracurricular', 'user'])->findOrFail($id);
        $extraName = $member->extracurricular?->name ?? 'ekstrakurikuler';
        $userId    = $member->user_id;

        \Illuminate\Support\Facades\DB::table('extracurricular_students')
            ->where('extracurricular_id', $member->extracurricular_id)
            ->where('student_id', $member->user_id)
            ->delete();

        $member->delete();

        if ($userId) {
            \App\Services\NotificationService::send(
                $userId,
                'Kepesertaan Ekstra Dibatalkan',
                "Kepesertaan Anda pada ekstrakurikuler {$extraName} telah dibatalkan oleh Sekolah.",
                'warning'
            );
        }

        return back()->with('success', 'Kepesertaan siswa berhasil dibatalkan/dihapus.');
    }
}
