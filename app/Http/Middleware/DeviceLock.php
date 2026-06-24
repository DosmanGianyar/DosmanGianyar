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

        // Akun belum memiliki device terdaftar — seharusnya tidak terjadi karena
        // binding dilakukan saat login, tapi kita tangani sebagai pengaman.
        if (! $user->hasDeviceLocked()) {
            return response()->json([
                'message' => 'Akun belum terikat ke perangkat. Silakan login ulang.',
                'code'    => 'DEVICE_NOT_BOUND',
            ], 403);
        }

        if (! $user->isDeviceAllowed($deviceId)) {
            return response()->json([
                'message' => 'Perangkat tidak diizinkan. Hubungi Admin untuk reset perangkat.',
                'code'    => 'DEVICE_MISMATCH',
            ], 403);
        }

        return $next($request);
    }
}
