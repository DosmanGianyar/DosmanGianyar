<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\EarlyCheckoutRequest;
use App\Models\Holiday;
use App\Services\GeofenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class AttendanceController extends Controller
{
    /**
     * Status presensi hari ini untuk user yang login.
     * Digunakan Flutter untuk menentukan tombol yang ditampilkan (check-in / check-out / selesai).
     */
    public function status(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user     = $request->user();
        $location = GeofenceService::getLocationForClass($user->class_id);
        $times    = $this->effectiveTimes($location, AttendanceSetting::current());
        $now      = now();

        $today     = $user->todayAttendance()->first();
        $isHoliday = Holiday::whereDate('date', today())->exists();

        return response()->json([
            'server_time' => $now->toIso8601String(),
            'is_holiday'  => $isHoliday || today()->isWeekend(),
            'shift'       => [
                'check_in_open'  => substr($times['check_in_open'], 0, 5),
                'check_in_late'  => substr($times['check_in_late'], 0, 5),
                'check_in_close' => substr($times['check_in_close'], 0, 5),
                'check_out_open' => substr($times['check_out_open'], 0, 5),
            ],
            'attendance'  => $today ? [
                'status'               => $today->status,
                'check_in_time'        => $today->check_in_time,
                'check_out_time'       => $today->check_out_time,
                'is_fake_gps'          => $today->is_fake_gps,
                'check_in_photo_url'   => $today->photo        ? Storage::url($today->photo)        : null,
                'check_out_photo_url'  => $today->check_out_photo ? Storage::url($today->check_out_photo) : null,
            ] : null,
            'can_checkin'   => $this->canCheckIn($today, $times, $now, $isHoliday),
            'can_checkout'  => $this->canCheckOut($today, $user->id, $times, $now),
        ]);
    }

    /**
     * Absen masuk — menerima foto (file multipart), latitude, longitude, accuracy.
     * Semua validasi (geofence, fake GPS, jam) dilakukan di backend menggunakan waktu server.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'photo'     => 'required|image|max:5120', // max 5 MB
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'required|numeric|min:0',
        ]);

        /** @var \App\Models\User $user */
        $user     = $request->user();
        $location = GeofenceService::getLocationForClass($user->class_id);
        $times    = $this->effectiveTimes($location, AttendanceSetting::current());
        $now      = now();

        // Hari libur
        if (Holiday::whereDate('date', today())->exists() || today()->isWeekend()) {
            return response()->json(['message' => 'Hari ini hari libur.', 'code' => 'HOLIDAY'], 422);
        }

        // Sudah absen masuk
        if ($user->todayAttendance()->whereIn('status', ['hadir', 'terlambat'])->exists()) {
            return response()->json(['message' => 'Kamu sudah absen masuk hari ini.', 'code' => 'ALREADY_CHECKED_IN'], 422);
        }

        // Validasi jam buka
        if ($now->lt(Carbon::today()->setTimeFromTimeString($times['check_in_open']))) {
            return response()->json([
                'message' => 'Absen belum dibuka. Mulai pukul ' . substr($times['check_in_open'], 0, 5) . '.',
                'code'    => 'NOT_YET_OPEN',
            ], 422);
        }

        // Validasi jam tutup
        if ($now->gte(Carbon::today()->setTimeFromTimeString($times['check_in_close']))) {
            return response()->json([
                'message' => 'Waktu absen masuk sudah berakhir.',
                'code'    => 'CHECKIN_CLOSED',
            ], 422);
        }

        // Anti Fake GPS — akurasi sempurna (< 5m) indikasi mock location
        if ((float) $request->accuracy < 5) {
            Attendance::updateOrCreate(
                ['user_id' => $user->id, 'date' => today()],
                ['status' => 'alpa', 'is_fake_gps' => true, 'device_info' => $request->userAgent()]
            );
            return response()->json([
                'message' => 'Terdeteksi Mock Location / Fake GPS. Presensi ditolak dan dicatat.',
                'code'    => 'FAKE_GPS_DETECTED',
            ], 422);
        }

        // Validasi Geofence (Haversine)
        if (! GeofenceService::isInsideZone((float) $request->latitude, (float) $request->longitude, $location)) {
            return response()->json([
                'message' => "Kamu berada di luar area {$location['name']} (radius {$location['radius']}m).",
                'code'    => 'OUTSIDE_GEOFENCE',
                'distance_meters' => (int) GeofenceService::haversineDistance(
                    $location['lat'], $location['lng'],
                    (float) $request->latitude, (float) $request->longitude
                ),
            ], 422);
        }

        // Simpan foto selfie (compressed 800px, quality 75)
        $filename   = 'selfies/' . $user->id . '_' . today()->format('Ymd') . '.jpg';
        $compressed = Image::read($request->file('photo'))->scaleDown(width: 800)->toJpeg(75);
        Storage::disk('public')->put($filename, $compressed);

        $status = $now->lt(Carbon::today()->setTimeFromTimeString($times['check_in_late']))
            ? 'hadir'
            : 'terlambat';

        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => today()],
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

        return response()->json([
            'message'        => 'Absen masuk berhasil!',
            'status'         => $status,
            'check_in_time'  => $now->format('H:i'),
            'server_time'    => $now->toIso8601String(),
        ]);
    }

    /**
     * Absen pulang.
     */
    public function checkOut(Request $request): JsonResponse
    {
        $request->validate([
            'photo'     => 'required|image|max:5120',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'required|numeric|min:0',
        ]);

        /** @var \App\Models\User $user */
        $user     = $request->user();
        $location = GeofenceService::getLocationForClass($user->class_id);
        $times    = $this->effectiveTimes($location, AttendanceSetting::current());
        $now      = now();

        $today = $user->todayAttendance()->whereIn('status', ['hadir', 'terlambat'])->first();

        if (! $today) {
            return response()->json(['message' => 'Belum absen masuk hari ini.', 'code' => 'NOT_CHECKED_IN'], 422);
        }

        if ($today->check_out_time) {
            return response()->json(['message' => 'Kamu sudah absen pulang hari ini.', 'code' => 'ALREADY_CHECKED_OUT'], 422);
        }

        // Cek waktu checkout (bypass jika ada izin pulang awal)
        $hasEarlyApproval = EarlyCheckoutRequest::approvedToday($user->id);
        if (! $hasEarlyApproval && $now->lt(Carbon::today()->setTimeFromTimeString($times['check_out_open']))) {
            return response()->json([
                'message' => 'Absen pulang belum dibuka. Mulai pukul ' . substr($times['check_out_open'], 0, 5) . '.',
                'code'    => 'CHECKOUT_NOT_OPEN',
            ], 422);
        }

        // Anti Fake GPS
        if ((float) $request->accuracy < 5) {
            return response()->json([
                'message' => 'Terdeteksi Mock Location / Fake GPS.',
                'code'    => 'FAKE_GPS_DETECTED',
            ], 422);
        }

        // Validasi Geofence
        if (! GeofenceService::isInsideZone((float) $request->latitude, (float) $request->longitude, $location)) {
            return response()->json([
                'message' => "Kamu berada di luar area {$location['name']}. Absen pulang tidak dapat dilakukan.",
                'code'    => 'OUTSIDE_GEOFENCE',
            ], 422);
        }

        $filename   = 'selfies/' . $user->id . '_' . today()->format('Ymd') . '_out.jpg';
        $compressed = Image::read($request->file('photo'))->scaleDown(width: 800)->toJpeg(75);
        Storage::disk('public')->put($filename, $compressed);

        $today->update([
            'check_out_time'  => $now->format('H:i:s'),
            'check_out_photo' => $filename,
        ]);

        return response()->json([
            'message'         => 'Absen pulang berhasil!',
            'check_out_time'  => $now->format('H:i'),
            'server_time'     => $now->toIso8601String(),
        ]);
    }

    /**
     * Riwayat presensi — navigasi per bulan.
     */
    public function history(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user  = $request->user();
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $month = max(1, min(12, $month));
        $year  = max(2020, min(now()->year + 1, $year));

        $start   = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end     = $start->copy()->endOfMonth();
        $rows = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->get(['date', 'check_in_time', 'check_out_time', 'status', 'is_fake_gps', 'photo', 'check_out_photo']);

        $records = $rows->map(fn ($r) => [
            'date'                => (string) $r->date,
            'check_in_time'       => $r->check_in_time,
            'check_out_time'      => $r->check_out_time,
            'status'              => $r->status,
            'is_fake_gps'         => (bool) $r->is_fake_gps,
            'check_in_photo_url'  => $r->photo            ? Storage::url($r->photo)            : null,
            'check_out_photo_url' => $r->check_out_photo  ? Storage::url($r->check_out_photo)  : null,
        ]);

        $summary = [
            'hadir'      => $rows->where('status', 'hadir')->count(),
            'terlambat'  => $rows->where('status', 'terlambat')->count(),
            'izin'       => $rows->where('status', 'izin')->count(),
            'sakit'      => $rows->where('status', 'sakit')->count(),
            'alpa'       => $rows->where('status', 'alpa')->count(),
            'dispensasi' => $rows->where('status', 'dispensasi')->count(),
        ];

        return response()->json([
            'month'       => $month,
            'year'        => $year,
            'summary'     => $summary,
            'records'     => $records,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function effectiveTimes(array $location, AttendanceSetting $global): array
    {
        return [
            'check_in_open'  => $location['check_in_open']  ?? $global->check_in_open,
            'check_in_late'  => $location['check_in_late']  ?? $global->check_in_late,
            'check_in_close' => $location['check_in_close'] ?? $global->check_in_close,
            'check_out_open' => $location['check_out_open'] ?? $global->check_out_open,
        ];
    }

    private function canCheckIn(?Attendance $today, array $times, Carbon $now, bool $isHoliday): bool
    {
        if ($isHoliday || today()->isWeekend()) return false;
        if ($today && in_array($today->status, ['hadir', 'terlambat'])) return false;
        if ($now->lt(Carbon::today()->setTimeFromTimeString($times['check_in_open']))) return false;
        if ($now->gte(Carbon::today()->setTimeFromTimeString($times['check_in_close']))) return false;
        return true;
    }

    private function canCheckOut(?Attendance $today, int $userId, array $times, Carbon $now): bool
    {
        if (! $today || ! in_array($today->status, ['hadir', 'terlambat'])) return false;
        if ($today->check_out_time) return false;
        if (EarlyCheckoutRequest::approvedToday($userId)) return true;
        return $now->gte(Carbon::today()->setTimeFromTimeString($times['check_out_open']));
    }
}
