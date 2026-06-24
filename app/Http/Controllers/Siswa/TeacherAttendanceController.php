<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SessionAttendance;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $siswa */
        $siswa   = auth()->user();
        $classId = $siswa->class_id;

        $month = $request->input('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);

        $records = TeacherAttendance::with(['teacher', 'subject'])
            ->where('class_id', $classId)
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->orderBy('date')
            ->orderBy('period')
            ->get();

        // Today's records for the student's class
        $today = TeacherAttendance::with(['teacher', 'subject'])
            ->where('class_id', $classId)
            ->where('date', today())
            ->orderBy('period')
            ->get();

        $summary = [
            'hadir'       => $records->where('status', 'hadir')->count(),
            'tidak_hadir' => $records->where('status', 'tidak_hadir')->count(),
            'izin'        => $records->where('status', 'izin')->count(),
            'sakit'       => $records->where('status', 'sakit')->count(),
        ];

        // Group records by date for easy display
        $byDate = $records->groupBy(fn($r) => $r->date->toDateString());

        return view('siswa.teacher-attendance.index', compact(
            'siswa', 'records', 'today', 'summary', 'byDate', 'month'
        ));
    }
}
