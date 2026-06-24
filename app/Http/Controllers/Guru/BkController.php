<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\BkLog;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BkController extends Controller
{
    public function index(Request $request): View
    {
        $classes         = SchoolClass::orderBy('name')->get();
        $selectedClassId = $request->get('class_id', $classes->first()?->id);

        // Students in selected class who have at least one BK log
        $students = User::where('role', 'siswa')
            ->where('class_id', $selectedClassId)
            ->withSum('conductLogs', 'point')
            ->withCount('bkLogs')
            ->having('bk_logs_count', '>', 0)
            ->orderBy('name')
            ->get();

        // All students in class for the dropdown
        $allStudents = User::where('role', 'siswa')
            ->where('class_id', $selectedClassId)
            ->orderBy('name')
            ->get();

        // Recent BK logs for the class
        $recentLogs = BkLog::with('student.schoolClass')
            ->whereHas('student', fn($q) => $q->where('class_id', $selectedClassId))
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        // Stats
        $stats = [
            'total_students'  => User::where('role', 'siswa')->where('class_id', $selectedClassId)->count(),
            'flagged'         => $students->count(),
            'auto_logs_today' => BkLog::whereDate('date', today())->where('is_auto', true)
                                    ->whereHas('student', fn($q) => $q->where('class_id', $selectedClassId))
                                    ->count(),
            'manual_logs'     => BkLog::where('is_auto', false)
                                    ->whereHas('student', fn($q) => $q->where('class_id', $selectedClassId))
                                    ->count(),
        ];

        return view('guru.bk.index', compact(
            'classes', 'selectedClassId', 'students',
            'allStudents', 'recentLogs', 'stats'
        ));
    }

    public function storeLog(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id'    => 'required|exists:users,id',
            'coaching_note' => 'required|string|max:1000',
            'date'          => 'required|date|before_or_equal:today',
        ]);

        BkLog::create([
            'student_id'    => $request->student_id,
            'counselor_id'  => Auth::id(),
            'coaching_note' => $request->coaching_note,
            'point_at_time' => User::find($request->student_id)->conductLogs()->sum('point'),
            'is_auto'       => false,
            'date'          => $request->date,
        ]);

        return back()->with('success', 'Catatan BK berhasil disimpan.');
    }
}
