<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeacherJournal;
use App\Models\TeacherJournalAbsence;
use App\Models\TujuanPembelajaran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuruJournalController extends Controller
{
    // GET /api/v1/guru/journals?class_id=&month=&year=&page=
    public function index(Request $request): JsonResponse
    {
        $teacher = Auth::user();

        $query = TeacherJournal::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name', 'tp:id,code,description'])
            ->withCount('absences')
            ->orderByDesc('date');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }

        $journals = $query->paginate(20);

        return response()->json([
            'data' => $journals->map(fn ($j) => $this->formatJournal($j)),
            'meta' => [
                'current_page' => $journals->currentPage(),
                'last_page'    => $journals->lastPage(),
                'total'        => $journals->total(),
            ],
        ]);
    }

    // POST /api/v1/guru/journals
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'             => 'required|exists:classes,id',
            'subject_id'           => 'nullable|exists:subjects,id',
            'date'                 => 'required|date',
            'period'               => 'nullable|integer|min:1|max:12',
            'period_end'           => 'nullable|integer|min:1|max:12|gte:period',
            'tp_id'                => 'nullable|exists:tujuan_pembelajaran,id',
            'learning_objectives'  => 'nullable|string|max:1000',
            'material'             => 'required|string|max:1000',
            'activity'             => 'required|string|max:1000',
            'notes'                => 'nullable|string|max:500',
            'absent_students'      => 'nullable|array',
            'absent_students.*.student_id' => 'required|exists:users,id',
            'absent_students.*.status'     => 'required|string|in:tidak_hadir,izin,sakit,dispensasi,alpa',
        ]);

        $teacher = Auth::user();

        // Resolve learning_objectives dari TP jika tp_id disediakan
        $lo = $request->learning_objectives;
        if ($request->filled('tp_id')) {
            $tp = TujuanPembelajaran::where('teacher_id', $teacher->id)
                ->find($request->tp_id);
            if ($tp) {
                $lo = ($tp->code ? "[{$tp->code}] " : '') . $tp->description;
            }
        }

        DB::beginTransaction();
        try {
            $journal = TeacherJournal::create([
                'teacher_id'          => $teacher->id,
                'class_id'            => $request->class_id,
                'subject_id'          => $request->subject_id,
                'tp_id'               => $request->tp_id,
                'date'                => $request->date,
                'period'              => $request->period,
                'period_end'          => $request->period_end,
                'learning_objectives' => $lo,
                'material'            => $request->material,
                'activity'            => $request->activity,
                'notes'               => $request->notes,
            ]);

            foreach ($request->absent_students ?? [] as $abs) {
                TeacherJournalAbsence::create([
                    'journal_id' => $journal->id,
                    'student_id' => $abs['student_id'],
                    'status'     => $abs['status'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Jurnal berhasil disimpan.',
                'id'      => $journal->id,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    // GET /api/v1/guru/journals/{id}
    public function show(int $id): JsonResponse
    {
        $teacher = Auth::user();
        $journal = TeacherJournal::where('teacher_id', $teacher->id)
            ->with([
                'schoolClass:id,name',
                'subject:id,name',
                'tp:id,code,description',
                'absences.student:id,name,nis',
            ])
            ->findOrFail($id);

        return response()->json($this->formatJournal($journal, withAbsences: true));
    }

    // PUT /api/v1/guru/journals/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $teacher = Auth::user();
        $journal = TeacherJournal::where('teacher_id', $teacher->id)->findOrFail($id);

        $request->validate([
            'learning_objectives'  => 'sometimes|required|string|max:1000',
            'material'             => 'sometimes|required|string|max:1000',
            'activity'             => 'sometimes|required|string|max:1000',
            'notes'                => 'nullable|string|max:500',
            'absent_students'      => 'nullable|array',
            'absent_students.*.student_id' => 'required|exists:users,id',
            'absent_students.*.status'     => 'required|string|in:tidak_hadir,izin,sakit,dispensasi,alpa',
        ]);

        DB::beginTransaction();
        try {
            $journal->update($request->only([
                'learning_objectives', 'material', 'activity', 'notes',
            ]));

            if ($request->has('absent_students')) {
                $journal->absences()->delete();
                foreach ($request->absent_students ?? [] as $abs) {
                    TeacherJournalAbsence::create([
                        'journal_id' => $journal->id,
                        'student_id' => $abs['student_id'],
                        'status'     => $abs['status'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Jurnal berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    // DELETE /api/v1/guru/journals/{id}
    public function destroy(int $id): JsonResponse
    {
        /** @var \App\Models\User $teacher */
        $teacher = Auth::user();

        $journal = TeacherJournal::find($id);

        if (! $journal) {
            return response()->json(['message' => 'Jurnal tidak ditemukan.'], 404);
        }

        $isOwner = (int) $journal->teacher_id === (int) $teacher->id;
        $isStaff = in_array($teacher->role, ['admin', 'piket']) || $teacher->isBk();

        if (! $isOwner && ! $isStaff) {
            return response()->json(['message' => 'Anda tidak memiliki wewenang menghapus jurnal ini.'], 403);
        }

        DB::beginTransaction();
        try {
            $journal->absences()->delete();
            $journal->delete();

            DB::commit();

            return response()->json(['message' => 'Jurnal berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus jurnal: ' . $e->getMessage()], 500);
        }
    }

    // GET /api/v1/guru/journals/class-students/{classId}?date=
    public function classStudents(Request $request, int $classId): JsonResponse
    {
        $date = $request->input('date', today()->toDateString());

        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        $studentIds  = $students->pluck('id')->toArray();
        $attendances = \App\Models\Attendance::whereIn('user_id', $studentIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        $result = $students->map(function ($s) use ($attendances) {
            $att = $attendances->get($s->id);
            $morningStatus = $att?->status;
            $isViaLupaAbsen = (bool) ($att?->via_lupa_absen ?? false);

            if ($isViaLupaAbsen || $morningStatus === 'lupa_absen') {
                $effectiveMorningStatus = 'lupa_absen';
                $morningStatusLabel = 'Lupa Absen';
            } elseif ($morningStatus === 'terlambat') {
                $effectiveMorningStatus = 'terlambat';
                $morningStatusLabel = 'Terlambat';
            } elseif ($morningStatus === 'hadir') {
                $effectiveMorningStatus = 'hadir';
                $morningStatusLabel = 'Hadir';
            } elseif ($morningStatus === 'sakit') {
                $effectiveMorningStatus = 'sakit';
                $morningStatusLabel = 'Sakit';
            } elseif ($morningStatus === 'izin') {
                $effectiveMorningStatus = 'izin';
                $morningStatusLabel = 'Izin';
            } elseif ($morningStatus === 'dispensasi') {
                $effectiveMorningStatus = 'dispensasi';
                $morningStatusLabel = 'Dispensasi';
            } elseif ($morningStatus === 'alpa') {
                $effectiveMorningStatus = 'alpa';
                $morningStatusLabel = 'Alpa';
            } else {
                $effectiveMorningStatus = 'belum_absen';
                $morningStatusLabel = 'Belum Absen Pagi';
            }

            $suggestedStatus = match ($effectiveMorningStatus) {
                'sakit'               => 'sakit',
                'izin'                => 'izin',
                'dispensasi'          => 'dispensasi',
                'alpa', 'belum_absen' => 'alpa',
                default               => 'hadir',
            };

            return [
                'id'                   => $s->id,
                'name'                 => $s->name,
                'nis'                  => $s->nis,
                'morning_status'       => $effectiveMorningStatus,
                'morning_status_label' => $morningStatusLabel,
                'suggested_status'     => $suggestedStatus,
                'via_lupa_absen'       => $isViaLupaAbsen,
            ];
        });

        return response()->json($result);
    }

    private function formatJournal(TeacherJournal $j, bool $withAbsences = false): array
    {
        $data = [
            'id'                   => $j->id,
            'class_id'             => $j->class_id,
            'class_name'           => $j->schoolClass?->name ?? '—',
            'subject_id'           => $j->subject_id,
            'subject_name'         => $j->subject?->name ?? '—',
            'date'                 => $j->date?->format('Y-m-d'),
            'period'               => $j->period,
            'period_end'           => $j->period_end,
            'tp_id'                => $j->tp_id,
            'tp_code'              => $j->tp?->code,
            'tp_description'       => $j->tp?->description,
            'learning_objectives'  => $j->learning_objectives,
            'material'             => $j->material,
            'activity'             => $j->activity,
            'notes'                => $j->notes,
            'absences_count'       => $j->absences_count ?? $j->absences->count(),
        ];

        if ($withAbsences) {
            $data['absent_students'] = $j->absences->map(fn ($a) => [
                'student_id'  => $a->student_id,
                'name'        => $a->student?->name ?? '—',
                'nis'         => $a->student?->nis ?? '—',
                'status'      => $a->status,
            ])->values();
        }

        return $data;
    }
}
