<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ConductLog;
use App\Models\ParentFeatureSeen;
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
        $childIds = $children->pluck('id')->all();

        $seens = ParentFeatureSeen::where('parent_id', $orangtua->id)
            ->whereIn('student_id', $childIds)
            ->get()
            ->keyBy(fn ($item) => "{$item->student_id}:{$item->feature}");

        $childrenData = $children->map(function (User $child) use ($seens) {
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

            $todayHoliday = \App\Models\Holiday::whereDate('date', today())
                ->where('type', 'libur')
                ->where(fn ($q) => $q
                    ->where('applies_to', 'semua')
                    ->orWhere(fn ($q2) => $q2
                        ->where('applies_to', 'kelas_tertentu')
                        ->whereHas('schoolClasses', fn ($q3) => $q3->where('classes.id', $child->class_id))
                    )
                )
                ->first();

            $isWeekend = today()->isSunday();

            // 1. Badge Absensi
            $latestAttendance = Attendance::where('user_id', $child->id)
                ->latest('date')
                ->latest('updated_at')
                ->first();

            $attSeen = $seens->get("{$child->id}:attendance");
            $hasUnreadAttendance = false;
            if ($latestAttendance) {
                if (! $attSeen) {
                    $hasUnreadAttendance = ($latestAttendance->date === today()->toDateString());
                } else {
                    $hasUnreadAttendance = $latestAttendance->updated_at?->gt($attSeen->last_seen_at) ?? false;
                }
            }

            // 2. Badge Catatan Karakter / SIPINTER
            $conductSeen = $seens->get("{$child->id}:conduct");
            $conductQuery = ConductLog::where('student_id', $child->id);
            if ($conductSeen?->last_seen_id) {
                $newConductCount = (clone $conductQuery)->where('id', '>', $conductSeen->last_seen_id)->count();
            } elseif ($conductSeen?->last_seen_at) {
                $newConductCount = (clone $conductQuery)->where('created_at', '>', $conductSeen->last_seen_at)->count();
            } else {
                $newConductCount = (clone $conductQuery)->where('created_at', '>=', now()->subDays(14))->count();
            }

            // 3. Badge Prestasi Siswa
            $achSeen = $seens->get("{$child->id}:achievement");
            $achQuery = StudentAchievement::where('student_id', $child->id);
            if ($achSeen?->last_seen_id) {
                $newAchCount = (clone $achQuery)->where('id', '>', $achSeen->last_seen_id)->count();
            } elseif ($achSeen?->last_seen_at) {
                $newAchCount = (clone $achQuery)->where('created_at', '>', $achSeen->last_seen_at)->count();
            } else {
                $newAchCount = (clone $achQuery)->where('created_at', '>=', now()->subDays(14))->count();
            }

            return [
                'student'           => $child,
                'today_attendance'  => $todayAttendance,
                'today_holiday'     => $todayHoliday,
                'is_weekend'        => $isWeekend,
                'monthly_holidays'  => $monthData['holidays'] ?? [],
                'monthly_summary'   => $monthData['summary'],
                'percentage'        => $monthData['attendance_percentage'],
                'total_days'        => $monthData['total_days'],
                'violation_count'   => $violationCount,
                'violation_points'  => $violationCount, // alias for backward compatibility
                'achievement_count' => $achievementCount,
                'badges'            => [
                    'attendance'  => [
                        'has_unread' => (bool)$hasUnreadAttendance,
                        'count'      => $hasUnreadAttendance ? 1 : 0,
                    ],
                    'conduct'     => [
                        'has_unread' => $newConductCount > 0,
                        'count'      => $newConductCount,
                    ],
                    'achievement' => [
                        'has_unread' => $newAchCount > 0,
                        'count'      => $newAchCount,
                    ],
                ],
            ];
        });

        return view('orangtua.dashboard', [
            'childrenData' => $childrenData,
        ]);
    }
}
