<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceLock
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $deviceId = $request->header('X-Device-ID');

        if (! $deviceId) {
            return response()->json([
                'message' => 'Header X-Device-ID wajib disertakan.',
                'code'    => 'MISSING_DEVICE_ID',
            ], 400);
        }

        // Periksa device. Jika user_devices belum ada (migration pending), lewati cek.
        try {
            if (! $user->hasDeviceLocked()) {
                // Device belum terdaftar: bisa karena migration belum jalan
                // atau login saat tabel belum ada. Izinkan lewat, device akan
                // terdaftar otomatis pada login berikutnya.
                return $next($request);
            }

            if (! $user->isDeviceRegistered($deviceId)) {
                return response()->json([
                    'message' => 'Perangkat tidak diizinkan. Hubungi Admin untuk reset perangkat.',
                    'code'    => 'DEVICE_MISMATCH',
                ], 403);
            }
        } catch (\Throwable $e) {
            // user_devices table may not exist yet — skip device check
        }

        return $next($request);
    }
}
