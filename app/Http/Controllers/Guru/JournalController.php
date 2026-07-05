<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherJournal;
use App\Models\TeacherJournalAbsence;
use App\Models\TujuanPembelajaran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user();
        $month   = $request->integer('month', now()->month);
        $year    = $request->integer('year', now()->year);
        $classId = $request->input('class_id');

        $query = TeacherJournal::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name', 'tp:id,code,description', 'absences'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderByDesc('date')
            ->orderByDesc('period');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $journals = $query->get();
        $classes  = SchoolClass::orderBy('name')->get();
        $total    = $journals->count();

        return view('guru.journal.index', compact('journals', 'classes', 'month', 'year', 'classId', 'total'));
    }

    public function create(): View
    {
        $teacher  = Auth::user();
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $tps = TujuanPembelajaran::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->with('subject:id,name')
            ->orderBy('subject_id')
            ->orderByDesc('id')
            ->get();

        return view('guru.journal.create', compact('classes', 'subjects', 'tps'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'class_id'                     => 'required|exists:classes,id',
            'subject_id'                   => 'nullable|exists:subjects,id',
            'date'                         => 'required|date',
            'period'                       => 'nullable|integer|min:1|max:12',
            'period_end'                   => 'nullable|integer|min:1|max:12|gte:period',
            'tp_id'                        => 'nullable|exists:tujuan_pembelajaran,id',
            'material'                     => 'required|string|max:1000',
            'activity'                     => 'required|string|max:1000',
            'notes'                        => 'nullable|string|max:500',
            'absent_students'              => 'nullable|array',
            'absent_students.*.student_id' => 'required|exists:users,id',
            'absent_students.*.status'     => 'required|in:tidak_hadir,izin,sakit',
        ]);

        $teacher = Auth::user();

        $lo = null;
        if ($request->filled('tp_id')) {
            $tp = TujuanPembelajaran::where('teacher_id', $teacher->id)->find($request->tp_id);
            if ($tp) {
                $lo = ($tp->code ? "[{$tp->code}] " : '') . $tp->description;
            }
        }

        DB::beginTransaction();
        try {
            $journal = TeacherJournal::create([
                'teacher_id'          => $teacher->id,
                'class_id'            => $request->class_id,
                'subject_id'          => $request->subject_id ?: null,
                'tp_id'               => $request->tp_id ?: null,
                'date'                => $request->date,
                'period'              => $request->period ?: null,
                'period_end'          => $request->period_end ?: null,
                'learning_objectives' => $lo,
                'material'            => $request->material,
                'activity'            => $request->activity,
                'notes'               => $request->notes ?: null,
            ]);

            foreach ($request->input('absent_students', []) as $abs) {
                if (!empty($abs['student_id']) && !empty($abs['status'])) {
                    TeacherJournalAbsence::create([
                        'journal_id' => $journal->id,
                        'student_id' => $abs['student_id'],
                        'status'     => $abs['status'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('guru.journal.index')
                ->with('success', 'Jurnal mengajar berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function print(Request $request): View
    {
        $teacher = Auth::user();
        $month   = $request->integer('month', now()->month);
        $year    = $request->integer('year', now()->year);
        $classId = $request->input('class_id');

        $query = TeacherJournal::where('teacher_id', $teacher->id)
            ->with(['schoolClass:id,name', 'subject:id,name', 'tp:id,code,description', 'absences.student:id,name,nis'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->orderBy('period');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $journals  = $query->get();
        $classes   = SchoolClass::orderBy('name')->get();
        $className = $classId ? SchoolClass::find($classId)?->name : null;

        return view('guru.journal.print', compact(
            'teacher', 'journals', 'classes',
            'month', 'year', 'classId', 'className'
        ));
    }

    public function destroy(TeacherJournal $journal): RedirectResponse
    {
        abort_unless($journal->teacher_id === Auth::id(), 403, 'Akses ditolak.');
        $journal->delete();
        return back()->with('success', 'Jurnal berhasil dihapus.');
    }

    public function studentsByClass(Request $request): JsonResponse
    {
        $classId  = $request->input('class_id');
        $students = User::where('role', 'siswa')
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        return response()->json($students);
    }
}
