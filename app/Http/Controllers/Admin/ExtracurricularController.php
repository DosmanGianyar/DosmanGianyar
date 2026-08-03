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
}
