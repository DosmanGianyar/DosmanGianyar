<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Attendance;
use App\Models\ConductLog;
use App\Models\ParentFeatureSeen;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Services\ChatService;
use App\Services\StudentDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrangtuaController extends Controller
{
    public function __construct(
        protected ?ChatService $chatService = null
    ) {
        $this->chatService = $chatService ?? app(ChatService::class);
    }

    public function children(): JsonResponse
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();

        $childrenModels = $orangtua->children()->with('schoolClass')->get();
        $childIds = $childrenModels->pluck('id')->all();

        // Ambil riwayat seen untuk semua anak sekaligus
        $seens = ParentFeatureSeen::where('parent_id', $orangtua->id)
            ->whereIn('student_id', $childIds)
            ->get()
            ->keyBy(fn ($item) => "{$item->student_id}:{$item->feature}");

        $chatTotalUnread = $this->chatService->getTotalUnreadCount($orangtua);

        $children = $childrenModels->map(function (User $c) use ($seens, $chatTotalUnread) {
            $monthData = StudentDataService::attendanceHistory($c, now()->month, now()->year);
            $todayAttendance = Attendance::where('user_id', $c->id)
                ->whereDate('date', today())
                ->first();

            $violationCount = ConductLog::where('student_id', $c->id)
                ->where(function ($query) {
                    $query->where('type', 'pelanggaran')
                          ->orWhereHas('category', fn ($q) => $q->where('type', 'pelanggaran'));
                })
                ->count();

            $achievementCount = StudentAchievement::where('student_id', $c->id)->count()
                + ConductLog::where('student_id', $c->id)
                    ->where(function ($query) {
                        $query->whereIn('type', ['prestasi', 'positif'])
                              ->orWhereHas('category', fn ($q) => $q->whereIn('type', ['prestasi', 'positif']));
                    })
                    ->count();

            // 1. Badge Absensi: apakah ada absensi yang belum dilihat sejak terakhir dibuka
            $latestAttendance = Attendance::where('user_id', $c->id)
                ->latest('date')
                ->latest('updated_at')
                ->first();

            $attSeen = $seens->get("{$c->id}:attendance");
            $hasUnreadAttendance = false;
            if ($latestAttendance) {
                if (! $attSeen) {
                    $hasUnreadAttendance = ($latestAttendance->date === today()->toDateString());
                } else {
                    $hasUnreadAttendance = $latestAttendance->updated_at?->gt($attSeen->last_seen_at) ?? false;
                }
            }

            // 2. Badge Catatan Karakter / SIPINTER: ada pelanggaran / catatan baru
            $conductSeen = $seens->get("{$c->id}:conduct");
            $conductQuery = ConductLog::where('student_id', $c->id);
            if ($conductSeen?->last_seen_id) {
                $newConductCount = (clone $conductQuery)->where('id', '>', $conductSeen->last_seen_id)->count();
            } elseif ($conductSeen?->last_seen_at) {
                $newConductCount = (clone $conductQuery)->where('created_at', '>', $conductSeen->last_seen_at)->count();
            } else {
                $newConductCount = (clone $conductQuery)->where('created_at', '>=', now()->subDays(14))->count();
            }

            // 3. Badge Prestasi Siswa:
            $achSeen = $seens->get("{$c->id}:achievement");
            $achQuery = StudentAchievement::where('student_id', $c->id);
            if ($achSeen?->last_seen_id) {
                $newAchCount = (clone $achQuery)->where('id', '>', $achSeen->last_seen_id)->count();
            } elseif ($achSeen?->last_seen_at) {
                $newAchCount = (clone $achQuery)->where('created_at', '>', $achSeen->last_seen_at)->count();
            } else {
                $newAchCount = (clone $achQuery)->where('created_at', '>=', now()->subDays(14))->count();
            }

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
                'badges'              => [
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
                    'chat'        => [
                        'has_unread' => $chatTotalUnread > 0,
                        'count'      => $chatTotalUnread,
                    ],
                ],
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

    public function markSeen(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer',
            'feature'    => 'required|string|in:attendance,conduct,achievement',
        ]);

        $child    = $this->resolveChild($request);
        /** @var User $orangtua */
        $orangtua = Auth::user();
        $feature  = $request->string('feature')->toString();

        $lastId = match ($feature) {
            'conduct'     => ConductLog::where('student_id', $child->id)->max('id'),
            'achievement' => StudentAchievement::where('student_id', $child->id)->max('id'),
            'attendance'  => Attendance::where('user_id', $child->id)->max('id'),
            default       => null,
        };

        ParentFeatureSeen::updateOrCreate(
            [
                'parent_id'  => $orangtua->id,
                'student_id' => $child->id,
                'feature'    => $feature,
            ],
            [
                'last_seen_at' => now(),
                'last_seen_id' => $lastId,
            ]
        );

        // Sinkronkan notifikasi in-app terkait agar terbaca jika relevan
        $notifTypes = match ($feature) {
            'attendance'  => ['attendance'],
            'conduct'     => ['conduct', 'warning'],
            'achievement' => ['achievement'],
            default       => [],
        };

        if (!empty($notifTypes)) {
            AppNotification::forUser($orangtua->id)
                ->unread()
                ->whereIn('type', $notifTypes)
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tanda telah dilihat berhasil diperbarui.',
        ]);
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
