<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ConductLog;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Services\StudentDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();

        $children = $orangtua->children()->with('schoolClass')->get();

        $childrenData = $children->map(function (User $child) {
            $monthData = StudentDataService::attendanceHistory($child, now()->month, now()->year);
            $todayAttendance = Attendance::where('user_id', $child->id)
                ->whereDate('date', today())
                ->first();

            $violationCount = ConductLog::where('student_id', $child->id)
                ->where(function ($query) {
                    $query->where('type', 'pelanggaran')
                          ->orWhereHas('category', fn ($q) => $q->where('type', 'pelanggaran'));
                })
                ->count();

            $achievementCount = StudentAchievement::where('student_id', $child->id)->count()
                + ConductLog::where('student_id', $child->id)
                    ->where(function ($query) {
                        $query->whereIn('type', ['prestasi', 'positif'])
                              ->orWhereHas('category', fn ($q) => $q->whereIn('type', ['prestasi', 'positif']));
                    })
                    ->count();

            return [
                'student'           => $child,
                'today_attendance'  => $todayAttendance,
                'monthly_summary'   => $monthData['summary'],
                'percentage'        => $monthData['attendance_percentage'],
                'total_days'        => $monthData['total_days'],
                'violation_count'   => $violationCount,
                'violation_points'  => $violationCount, // alias for backward compatibility
                'achievement_count' => $achievementCount,
            ];
        });

        return view('orangtua.dashboard', [
            'childrenData' => $childrenData,
        ]);
    }
}
