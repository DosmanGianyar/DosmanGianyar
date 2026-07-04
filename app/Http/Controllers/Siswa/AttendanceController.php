<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappJob;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\EarlyCheckoutRequest;
use App\Models\Holiday;
use App\Services\GeofenceService;
use App\Services\WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    /**
     * Resolve effective time settings for a class.
     * Location-specific overrides take priority over global settings.
     */
    private function effectiveTimes(array $location, AttendanceSetting $global): array
    {
        return [
            'check_in_open'  => $location['check_in_open']  ?? $global->check_in_open,
            'check_in_late'  => $location['check_in_late']  ?? $global->check_in_late,
            'check_in_close' => $location['check_in_close'] ?? $global->check_in_close,
            'check_out_open' => $location['check_out_open'] ?? $global->check_out_open,
        ];
    }

    public function locationCheck(): View|RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa    = Auth::user();
        $location = GeofenceService::getLocationForClass($siswa->class_id);
        $times    = $this->effectiveTimes($location, AttendanceSetting::current());
        $now      = now();

        // Sudah check-in hari ini? → masuk fase checkout
        $today = $siswa->todayAttendance()->first();
        if ($today && in_array($today->status, ['hadir', 'terlambat'])) {
            if ($today->check_out_time) {
                return redirect()->route('siswa.dashboard')
                    ->with('success', 'Kamu sudah absen masuk & pulang hari ini.');
            }

            $hasEarlyApproval = EarlyCheckoutRequest::approvedToday($siswa->id);
            $checkOutTooEarly = ! $hasEarlyApproval
                && $now->lt(Carbon::today()->setTimeFromTimeString($times['check_out_open']));

            return view('siswa.attendance.location', [
                'location'         => $location,
                'isCheckOut'       => true,
                'attendance'       => $today,
                'checkOutTooEarly' => $checkOutTooEarly,
                'checkOutOpen'     => substr($times['check_out_open'], 0, 5),
                'hasEarlyApproval' => $hasEarlyApproval,
                'isClosed'         => false,
            ]);
        }

        // Hari libur?
        if (Holiday::isOffDayFor(today(), $siswa->class_id)) {
            return redirect()->route('siswa.dashboard')
                ->with('success', 'Hari ini hari libur. Presensi tidak diperlukan.');
        }

        $notYetOpen = $now->lt(Carbon::today()->setTimeFromTimeString($times['check_in_open']));
        $isClosed   = $now->gte(Carbon::today()->setTimeFromTimeString($times['check_in_close']));

        return view('siswa.attendance.location', [
            'location'     => $location,
            'isClosed'     => $isClosed,
            'notYetOpen'   => $notYetOpen,
            'checkInOpen'  => substr($times['check_in_open'], 0, 5),
            'checkInLate'  => substr($times['check_in_late'], 0, 5),
            'checkInClose' => substr($times['check_in_close'], 0, 5),
            'isCheckOut'   => false,
        ]);
    }

    public function show(): View|RedirectResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa    = Auth::user();
        $location = GeofenceService::getLocationForClass($siswa->class_id);
        $times    = $this->effectiveTimes($location, AttendanceSetting::current());
        $now      = now();

        // Sudah presensi hari ini?
        $today = $siswa->todayAttendance()->first();
        if ($today && in_array($today->status, ['hadir', 'terlambat'])) {
            if ($today->check_out_time) {
                return redirect()->route('siswa.dashboard')
                    ->with('success', 'Kamu sudah absen masuk & pulang hari ini.');
            }

            $hasEarlyApproval = EarlyCheckoutRequest::approvedToday($siswa->id);
            $checkOutTooEarly = ! $hasEarlyApproval
                && $now->lt(Carbon::today()->setTimeFromTimeString($times['check_out_open']));

            return view('siswa.attendance.selfie', [
                'siswa'            => $siswa,
                'isClosed'         => false,
                'location'         => $location,
                'isCheckOut'       => true,
                'attendance'       => $today,
                'checkOutTooEarly' => $checkOutTooEarly,
                'checkOutOpen'     => substr($times['check_out_open'], 0, 5),
                'hasEarlyApproval' => $hasEarlyApproval,
            ]);
        }

        // Hari libur?
        if (Holiday::isOffDayFor(today(), $siswa->class_id)) {
            return redirect()->route('siswa.dashboard')
                ->with('success', 'Hari ini hari libur. Presensi tidak diperlukan.');
        }

        $notYetOpen = $now->lt(Carbon::today()->setTimeFromTimeString($times['check_in_open']));
        $isClosed   = $now->gte(Carbon::today()->setTimeFromTimeString($times['check_in_close']));

        return view('siswa.attendance.selfie', [
            'siswa'        => $siswa,
            'isClosed'     => $isClosed,
            'notYetOpen'   => $notYetOpen,
            'checkInOpen'  => substr($times['check_in_open'], 0, 5),
            'checkInLate'  => substr($times['check_in_late'], 0, 5),
            'checkInClose' => substr($times['check_in_close'], 0, 5),
            'location'     => $location,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'photo'     => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy'  => 'required|numeric',
        ]);

        /** @var \App\Models\User $siswa */
        $siswa    = Auth::user();
        $location = GeofenceService::getLocationForClass($siswa->class_id);
        $times    = $this->effectiveTimes($location, AttendanceSetting::current());
        $now      = now();

        // Sudah presensi?
        if ($siswa->todayAttendance()->whereIn('status', ['hadir', 'terlambat'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Kamu sudah presensi hari ini.'], 422);
        }

        // Cek rentang waktu absen masuk
        if ($now->lt(Carbon::today()->setTimeFromTimeString($times['check_in_open']))) {
            return response()->json([
                'success' => false,
                'message' => 'Absen belum dibuka. Presensi bisa dilakukan mulai pukul ' . substr($times['check_in_open'], 0, 5) . '.',
            ], 422);
        }

        if ($now->gte(Carbon::today()->setTimeFromTimeString($times['check_in_close']))) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu absen masuk sudah berakhir (pukul ' . substr($times['check_in_close'], 0, 5) . ').',
            ], 422);
        }

        // Anti Fake GPS — akurasi terlalu sempurna (< 5m) = curiga mock location
        if ((float) $request->accuracy < 5) {
            Attendance::updateOrCreate(
                ['user_id' => $siswa->id, 'date' => today()],
                ['status' => 'alpa', 'is_fake_gps' => true, 'device_info' => $request->userAgent()]
            );
            return response()->json([
                'success' => false,
                'message' => 'Terdeteksi Mock Location / Fake GPS. Presensi ditolak dan dicatat.',
            ], 422);
        }

        // Validasi Geofence (Haversine)
        if (!GeofenceService::isInsideZone((float) $request->latitude, (float) $request->longitude, $location)) {
            return response()->json([
                'success' => false,
                'message' => "Kamu berada di luar area {$location['name']} (radius {$location['radius']}m). Presensi tidak dapat dilakukan.",
            ], 422);
        }

        // Simpan foto selfie (compressed 800px, quality 75)
        $base64     = preg_replace('#^data:image/\w+;base64,#i', '', $request->photo);
        $imgData    = base64_decode($base64);
        $filename   = 'selfies/' . $siswa->id . '_' . today()->format('Ymd') . '.jpg';
        $compressed = Image::read($imgData)->scaleDown(width: 800)->toJpeg(75);
        Storage::disk('public')->put($filename, $compressed);

        // Tentukan status berdasarkan setting efektif
        $status = $now->lt(Carbon::today()->setTimeFromTimeString($times['check_in_late']))
            ? 'hadir'
            : 'terlambat';

        Attendance::updateOrCreate(
            ['user_id' => $siswa->id, 'date' => today()],
            [
                'check_in_time' => $now->format('H:i:s'),
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
                'photo'         => $filename,
                'status'        => $status,
                'device_info'   => $request->userAgent(),
                'is_fake_gps'   => false,
            ]
        );

        // Kirim notifikasi WA ke orang tua
        if ($siswa->parent_phone) {
            $wa      = new WhatsappService();
            $kelas   = $siswa->schoolClass?->name ?? '-';
            $message = $wa->templateCheckIn(
                parentName  : $siswa->parent_name  ?? 'Orang Tua',
                studentName : $siswa->name,
                className   : $kelas,
                status      : $status,
                time        : $now->format('H:i')
            );
            SendWhatsappJob::dispatch($siswa->parent_phone, $message);
        }

        $label = $status === 'hadir' ? 'Hadir' : 'Terlambat';
        return response()->json([
            'success' => true,
            'status'  => $status,
            'message' => "Presensi berhasil! Status: {$label} — {$now->format('H:i')}",
        ]);
    }

    public function history(Request $request): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $month = max(1, min(12, $month));
        $year  = max(2020, min(now()->year + 1, $year));

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $records = Attendance::where('user_id', $siswa->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->get();

        $approvedDates = EarlyCheckoutRequest::where('student_id', $siswa->id)
            ->whereBetween('date', [$start, $end])
            ->where('status', 'approved')
            ->pluck('date')
            ->mapWithKeys(fn($d) => [$d->format('Y-m-d') => true])
            ->all();

        $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'dispensasi' => 0];
        $effectiveStatuses = [];
        foreach ($records as $rec) {
            $dateStr = $rec->date->format('Y-m-d');
            $effective = $rec->effectiveStatus(isset($approvedDates[$dateStr]));
            $effectiveStatuses[$dateStr] = $effective;
            if (isset($summary[$effective])) $summary[$effective]++;
        }

        // Monthly trend: last 6 months
        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $count = Attendance::where('user_id', $siswa->id)
                ->whereYear('date', $m->year)
                ->whereMonth('date', $m->month)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();
            $trend->push(['label' => $m->isoFormat('MMM'), 'count' => $count]);
        }

        $prevMonth = $start->copy()->subMonth();
        $nextMonth = $start->copy()->addMonth();
        $canNext   = $nextMonth->lte(now()->endOfMonth());

        return view('siswa.attendance.history', compact(
            'siswa', 'records', 'summary', 'trend', 'month', 'year',
            'start', 'prevMonth', 'nextMonth', 'canNext', 'effectiveStatuses'
        ));
    }

    public function storeCheckOut(Request $request): JsonResponse
    {
        $request->validate([
            'photo'     => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy'  => 'required|numeric',
        ]);

        /** @var \App\Models\User $siswa */
        $siswa    = Auth::user();
        $location = GeofenceService::getLocationForClass($siswa->class_id);
        $times    = $this->effectiveTimes($location, AttendanceSetting::current());
        $now      = now();

        $today = $siswa->todayAttendance()->whereIn('status', ['hadir', 'terlambat'])->first();

        if (! $today) {
            return response()->json(['success' => false, 'message' => 'Kamu belum melakukan absen masuk hari ini.'], 422);
        }

        if ($today->check_out_time) {
            return response()->json(['success' => false, 'message' => 'Kamu sudah melakukan absen pulang hari ini.'], 422);
        }

        // Cek batas waktu absen pulang (lewati jika ada izin pulang awal yang disetujui)
        $hasEarlyApproval = EarlyCheckoutRequest::approvedToday($siswa->id);
        if (! $hasEarlyApproval && $now->lt(Carbon::today()->setTimeFromTimeString($times['check_out_open']))) {
            return response()->json([
                'success' => false,
                'message' => 'Absen pulang belum dibuka. Bisa dilakukan mulai pukul ' . substr($times['check_out_open'], 0, 5) . '.',
            ], 422);
        }

        if ((float) $request->accuracy < 5) {
            return response()->json(['success' => false, 'message' => 'Terdeteksi Mock Location / Fake GPS.'], 422);
        }

        if (! GeofenceService::isInsideZone((float) $request->latitude, (float) $request->longitude, $location)) {
            return response()->json([
                'success' => false,
                'message' => "Kamu berada di luar area {$location['name']}. Absen pulang tidak dapat dilakukan.",
            ], 422);
        }

        $base64     = preg_replace('#^data:image/\w+;base64,#i', '', $request->photo);
        $imgData    = base64_decode($base64);
        $filename   = 'selfies/' . $siswa->id . '_' . today()->format('Ymd') . '_out.jpg';
        $compressed = Image::read($imgData)->scaleDown(width: 800)->toJpeg(75);
        Storage::disk('public')->put($filename, $compressed);

        $now = now();
        $today->update([
            'check_out_time'  => $now->format('H:i:s'),
            'check_out_photo' => $filename,
        ]);

        // Kirim notifikasi WA ke orang tua
        if ($siswa->parent_phone) {
            $wa      = new WhatsappService();
            $kelas   = $siswa->schoolClass?->name ?? '-';
            $message = $wa->templateCheckOut(
                parentName  : $siswa->parent_name ?? 'Orang Tua',
                studentName : $siswa->name,
                className   : $kelas,
                time        : $now->format('H:i')
            );
            SendWhatsappJob::dispatch($siswa->parent_phone, $message);
        }

        return response()->json([
            'success' => true,
            'message' => "Absen pulang berhasil! Pukul {$now->format('H:i')}",
        ]);
    }
}
