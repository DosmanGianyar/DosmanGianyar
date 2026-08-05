<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrangtuaController extends Controller
{
    public function children(): JsonResponse
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();

        $children = $orangtua->children()->with('schoolClass')->get()->map(function (User $c) {
            $monthData = StudentDataService::attendanceHistory($c, now()->month, now()->year);
            $todayAttendance = \App\Models\Attendance::where('user_id', $c->id)
                ->whereDate('date', today())
                ->first();

            $violationCount = \App\Models\ConductLog::where('student_id', $c->id)
                ->where(function ($query) {
                    $query->where('type', 'pelanggaran')
                          ->orWhereHas('category', fn ($q) => $q->where('type', 'pelanggaran'));
                })
                ->count();

            $achievementCount = \App\Models\StudentAchievement::where('student_id', $c->id)->count()
                + \App\Models\ConductLog::where('student_id', $c->id)
                    ->where(function ($query) {
                        $query->whereIn('type', ['prestasi', 'positif'])
                              ->orWhereHas('category', fn ($q) => $q->whereIn('type', ['prestasi', 'positif']));
                    })
                    ->count();

            return [
                'id'                  => $c->id,
                'name'                => $c->name,
                'class_name'          => $c->schoolClass?->name,
                'photo_url'           => $c->photo_url,
                'attendance_percentage' => $monthData['attendance_percentage'],
                'monthly_summary'     => $monthData['summary'],
                'violation_count'     => $violationCount,
                'violation_points'    => $violationCount,
                'achievement_count'   => $achievementCount,
                'today_attendance'    => $todayAttendance ? [
                    'status'              => $todayAttendance->status,
                    'check_in_time'       => $todayAttendance->check_in_time,
                    'check_out_time'      => $todayAttendance->check_out_time,
                    'check_in_photo_url'  => $todayAttendance->photo_url,
                    'check_out_photo_url' => $todayAttendance->check_out_photo_url,
                ] : null,
            ];
        })->values();

        return response()->json(['children' => $children]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $child = $this->resolveChild($request);
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        return response()->json(StudentDataService::attendanceHistory($child, $month, $year));
    }

    public function conduct(Request $request): JsonResponse
    {
        return response()->json(StudentDataService::conductLogs($this->resolveChild($request)));
    }

    public function achievements(Request $request): JsonResponse
    {
        return response()->json(StudentDataService::achievements($this->resolveChild($request)));
    }

    /** Resolve & pastikan student_id yang diminta memang anak dari orangtua yang login. */
    private function resolveChild(Request $request): User
    {
        $request->validate(['student_id' => 'required|integer']);

        /** @var User $orangtua */
        $orangtua = Auth::user();

        $child = $orangtua->children()->where('users.id', $request->integer('student_id'))->first();

        abort_if(! $child, 403, 'Anda tidak memiliki akses ke data siswa ini.');

        return $child;
    }
}
