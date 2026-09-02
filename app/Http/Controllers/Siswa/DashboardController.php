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
            'photo'       => $todayAtt?->photo_url,
            'checked_in'  => $todayAtt && in_array($todayAtt->status, ['hadir', 'terlambat']),
            'check_out_time'  => $todayAtt?->check_out_time
                ? Carbon::parse($todayAtt->check_out_time)->format('H:i')
                : null,
            'check_out_photo' => $todayAtt?->check_out_photo_url,
        ];

        // ─── Conduct Summary ──────────────────────────────────────────
        $logs          = $siswa->conductLogs()->with('category')->latest()->get();
        $prestasiCount = $logs->filter(fn($l) => $l->isPrestasi())->count();
        $pelanggaranCount = $logs->filter(fn($l) => $l->isPelanggaran())->count();
        $pointSummary  = [
            'total'       => $logs->count(),
            'prestasi'    => $prestasiCount,
            'pelanggaran' => $pelanggaranCount,
        ];

        // ─── Recent 3 Conduct Logs ────────────────────────────────────
        $recentPoints = $logs->take(3)->map(fn($log) => [
            'date'  => $log->created_at->toDateString(),
            'type'  => $log->isPrestasi() ? 'prestasi' : 'pelanggaran',
            'desc'  => $log->displayCategoryName(),
            'point' => $log->isPrestasi() ? 'Catatan Positif' : 'Catatan Negatif',
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

        $approvedPermits = \App\Models\Permit::where('student_id', $siswa->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('start_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('end_date', [$monthStart, $monthEnd])
                  ->orWhere(function ($q2) use ($monthStart, $monthEnd) {
                      $q2->where('start_date', '<=', $monthStart)
                         ->where('end_date', '>=', $monthEnd);
                  });
            })
            ->get();

        $permitMap = [];
        foreach ($approvedPermits as $permit) {
            $pStart = max($monthStart->copy(), $permit->start_date);
            $pEnd   = min($monthEnd->copy(), $permit->end_date);
            for ($dt = $pStart->copy(); $dt->lte($pEnd); $dt->addDay()) {
                $permitMap[$dt->format('Y-m-d')] = $permit->type;
            }
        }

        $monthlyHolidays = Holiday::getHolidayDates($monthStart, $monthEnd, $siswa->class_id);
        $monthlySpecial  = Holiday::getSpecialSchoolDates($monthStart, $monthEnd, $siswa->class_id);

        // Build per-day map with effective status applied (ignore alpa on non-school days)
        $monthlyByDate = [];
        $recordedDates = [];
        foreach ($monthlyRecs as $rec) {
            $ds = $rec->date->format('Y-m-d');
            $isSchool = Holiday::isSchoolDay($rec->date, $monthlyHolidays, $monthlySpecial);
            if (! $isSchool && $rec->status === 'alpa') continue;

            $recordedDates[$ds] = true;
            $monthlyByDate[$ds] = $rec->effectiveStatus(isset($monthlyApproved[$ds]));
        }

        // Add permit status or alpa for past school days with no record
        $today = today();
        for ($day = $monthStart->copy(); $day->lt($today); $day->addDay()) {
            $ds = $day->format('Y-m-d');
            if (! Holiday::isSchoolDay($day, $monthlyHolidays, $monthlySpecial)) continue;
            if (isset($recordedDates[$ds])) continue;
            
            if (isset($permitMap[$ds])) {
                $monthlyByDate[$ds] = $permitMap[$ds];
            } else {
                $monthlyByDate[$ds] = 'alpa';
            }
        }

        $monthlySummary = ['terlambat' => 0, 'alpa' => 0, 'izin' => 0, 'sakit' => 0, 'dispensasi' => 0];
        foreach ($monthlyByDate as $effStatus) {
            if (isset($monthlySummary[$effStatus])) $monthlySummary[$effStatus]++;
        }

        $qrContent = url('/verifikasi/kartu-pelajar/' . $siswa->qr_token);
        $options   = new \chillerlan\QRCode\QROptions(['outputType' => 'svg']);
        $qrSvg     = (new \chillerlan\QRCode\QRCode($options))->render($qrContent);

        $popupAnnouncement = Announcement::activeModal('siswa', $siswa->class_id)->first();

        $myExtracurricularRoles = $siswa->extracurricularsAsStudent()->get();

        return view('siswa.dashboard', compact(
            'siswa', 'todayStatus', 'pointSummary',
            'recentPoints', 'announcements', 'unreadNotifications',
            'monthlySummary', 'monthlyByDate', 'monthlyHolidays', 'monthlySpecial', 'qrSvg',
            'myExtracurricularRoles', 'popupAnnouncement'
        ));
    }
}
