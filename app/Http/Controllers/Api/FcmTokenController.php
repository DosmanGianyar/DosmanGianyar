<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Simpan atau perbarui token FCM perangkat user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token'   => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        $token = UserFcmToken::updateOrCreate(
            [
                'user_id'   => $request->user()->id,
                'fcm_token' => $request->fcm_token,
            ],
            [
                'device_type'  => $request->device_type ?? 'android',
                'device_name'  => $request->device_name,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'FCM Token registered successfully.',
            'token'   => $token,
        ]);
    }

    /**
     * Hapus token FCM saat user logout.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        UserFcmToken::where('user_id', $request->user()->id)
            ->where('fcm_token', $request->fcm_token)
            ->delete();

        return response()->json([
            'message' => 'FCM Token removed successfully.',
        ]);
    }

    /**
     * Kirim notifikasi uji coba ke perangkat user yang sedang login.
     */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();

        \App\Services\NotificationService::send(
            $user->id,
            '🔔 Uji Coba Push Notifikasi SIMS',
            "Halo {$user->name}, Push Notifikasi SIMS di perangkat Anda telah terhubung dan berfungsi aktif! (" . now()->format('H:i:s') . " WITA)",
            'info'
        );

        return response()->json([
            'message' => 'Notifikasi uji coba berhasil dikirim ke perangkat Anda.',
        ]);
    }
}
