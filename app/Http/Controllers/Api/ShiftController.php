<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Services\GeofenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Mengembalikan informasi shift aktif + lokasi geofence + waktu server.
     * Flutter menggunakan ini untuk:
     *   1. Menampilkan jam buka/tutup absen
     *   2. Mengetahui titik pusat & radius geofence untuk preview peta
     *   3. Mensinkronkan waktu server (hindari manipulasi jam HP)
     */
    public function active(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user     = $request->user();
        $now      = now();
        $location = GeofenceService::getLocationForClass($user->class_id);
        $global   = AttendanceSetting::current();

        $times = [
            'check_in_open'  => $location['check_in_open']  ?? $global->check_in_open,
            'check_in_late'  => $location['check_in_late']  ?? $global->check_in_late,
            'check_in_close' => $location['check_in_close'] ?? $global->check_in_close,
            'check_out_open' => $location['check_out_open'] ?? $global->check_out_open,
        ];

        return response()->json([
            'server_time' => $now->toIso8601String(),
            'timezone'    => config('app.timezone'),
            'shift'       => [
                'check_in_open'  => substr($times['check_in_open'], 0, 5),
                'check_in_late'  => substr($times['check_in_late'], 0, 5),
                'check_in_close' => substr($times['check_in_close'], 0, 5),
                'check_out_open' => substr($times['check_out_open'], 0, 5),
            ],
            'location' => [
                'name'           => $location['name'],
                'latitude'       => $location['lat'],
                'longitude'      => $location['lng'],
                'radius_meters'  => $location['radius'],
            ],
        ]);
    }
}
