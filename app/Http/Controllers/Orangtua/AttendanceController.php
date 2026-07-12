<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function history(Request $request, int $studentId): View
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();
        $student  = $orangtua->children()->where('users.id', $studentId)->firstOrFail();

        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $data = StudentDataService::attendanceHistory($student, $month, $year);

        $start     = \Illuminate\Support\Carbon::createFromDate($data['year'], $data['month'], 1)->startOfMonth();
        $prevMonth = $start->copy()->subMonth();
        $nextMonth = $start->copy()->addMonth();
        $canNext   = $nextMonth->lte(now()->endOfMonth());

        return view('orangtua.attendance.history', [
            'student'   => $student,
            'records'   => $data['records'],
            'summary'   => $data['summary'],
            'start'     => $start,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'canNext'   => $canNext,
        ]);
    }
}
