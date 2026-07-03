<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AppNotification;
use App\Models\EarlyCheckoutRequest;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user()->load('schoolClass');

        // ─── Today's Attendance Status ────────────────────────────────
        $todayAtt   = Attendance::where('user_id', $siswa->id)->whereDate('date', today())->first();
        $labelMap   = [
            'hadir' => 'Hadir', 'terlambat' => 'Terlambat',
            'izin' => 'Izin', 'sakit' => 'Sakit',
            'alpa' => 'Alpa', 'dispensasi' => 'Dispensasi',
        ];
        $todayStatus = [
            'status' => $todayAtt ? ($labelMap[$todayAtt->status] ?? ucfirst($todayAtt->status)) : 'Belum Presensi',
            'time'   => $todayAtt?->check_in_time
                ? Carbon::parse($todayAtt->check_in_time)->format('H:i')
                : '—',
            'color'  => match ($todayAtt?->status) {
                'hadir'      => 'green',
                'terlambat'  => 'yellow',
                'izin', 'sakit', 'dispensasi' => 'blue',
                'alpa'       => 'red',
                default      => 'gray',
            },
            'photo'       => $todayAtt?->photo,
            'checked_in'  => $todayAtt && in_array($todayAtt->status, ['hadir', 'terlambat']),
            'check_out_time'  => $todayAtt?->check_out_time
                ? Carbon::parse($todayAtt->check_out_time)->format('H:i')
                : null,
            'check_out_photo' => $todayAtt?->check_out_photo,
        ];

        // ─── Point Summary ────────────────────────────────────────────
        $logs          = $siswa->conductLogs()->with('category')->latest()->get();
        $prestasi      = $logs->where('point', '>', 0)->sum('point');
        $pelanggaran   = abs($logs->where('point', '<', 0)->sum('point'));
        $pointSummary  = [
            'total'       => (int) $logs->sum('point'),
            'prestasi'    => (int) $prestasi,
            'pelanggaran' => (int) $pelanggaran,
        ];

        // ─── Recent 3 Conduct Logs ────────────────────────────────────
        $recentPoints = $logs->take(3)->map(fn($log) => [
            'date'  => $log->created_at->toDateString(),
            'type'  => $log->point >= 0 ? 'prestasi' : 'pelanggaran',
            'desc'  => $log->category?->name ?? $log->note ?? '—',
            'point' => ($log->point >= 0 ? '+' : '') . $log->point,
        ]);

        // ─── Recent Announcements ─────────────────────────────────────
        $announcements = Announcement::published()
            ->forRole('siswa')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'title' => $a->title,
                'date'  => $a->published_at->toDateString(),
                'id'    => $a->id,
            ]);

        // ─── Unread Notifications Count ───────────────────────────────
        $unreadNotifications = AppNotification::forUser($siswa->id)->unread()->count();

        // ─── Monthly Attendance Mini-Summary & Calendar ───────────────
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        $monthlyRecs = Attendance::where('user_id', $siswa->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get(['date', 'status', 'check_in_time', 'check_out_time']);

        $monthlyApproved = EarlyCheckoutRequest::where('student_id', $siswa->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->where('status', 'approved')
            ->pluck('date')
            ->mapWithKeys(fn($d) => [$d->format('Y-m-d') => true])
            ->all();

        $monthlyHolidays = Holiday::whereBetween('date', [$monthStart, $monthEnd])
            ->pluck('date')
            ->mapWithKeys(fn($d) => [$d->format('Y-m-d') => true])
            ->all();

        // Build per-day map with effective status applied
        $monthlyByDate = [];
        $recordedDates = [];
        foreach ($monthlyRecs as $rec) {
            $ds = $rec->date->format('Y-m-d');
            $recordedDates[$ds] = true;
            $monthlyByDate[$ds] = $rec->effectiveStatus(isset($monthlyApproved[$ds]));
        }

        // Add alpa for past school days with no record
        $today = today();
        for ($day = $monthStart->copy(); $day->lt($today); $day->addDay()) {
            $ds = $day->format('Y-m-d');
            if ($day->isWeekend() || isset($monthlyHolidays[$ds]) || isset($recordedDates[$ds])) continue;
            $monthlyByDate[$ds] = 'alpa';
        }

        $monthlySummary = ['terlambat' => 0, 'alpa' => 0, 'izin' => 0, 'sakit' => 0, 'dispensasi' => 0];
        foreach ($monthlyByDate as $effStatus) {
            if (isset($monthlySummary[$effStatus])) $monthlySummary[$effStatus]++;
        }

        return view('siswa.dashboard', compact(
            'siswa', 'todayStatus', 'pointSummary',
            'recentPoints', 'announcements', 'unreadNotifications',
            'monthlySummary', 'monthlyByDate'
        ));
    }
}
