<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFcmToken;
use App\Services\FcmService;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Store or update FCM Token for logged-in user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token'   => 'required|string',
            'device_type' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        $user = $request->user();

        UserFcmToken::updateOrCreate(
            [
                'user_id'   => $user->id,
                'fcm_token' => $request->fcm_token,
            ],
            [
                'device_type'  => $request->device_type ?? 'android',
                'device_name'  => $request->device_name,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM Token registered successfully.',
        ]);
    }

    /**
     * Remove FCM Token on user logout.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        UserFcmToken::where('user_id', $request->user()->id)
            ->where('fcm_token', $request->fcm_token)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'FCM Token unregistered successfully.',
        ]);
    }

    /**
     * Send test FCM notification to current logged-in user.
     */
    public function test(Request $request)
    {
        $user = $request->user();

        // 1. Simpan pesan notifikasi ke database agar terbaca di daftar notifikasi
        try {
            \App\Models\Notification::create([
                'user_id'    => $user->id,
                'title'      => '🔔 Uji Coba Push Notifikasi SIMS',
                'body'       => 'Halo ' . $user->name . ', notifikasi uji coba berhasil dikirim & terbaca pada sistem.',
                'type'       => 'success',
                'is_read'    => false,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to save test notification to DB: ' . $e->getMessage());
        }

        $sent = FcmService::sendToUsers(
            [$user->id],
            '🔔 Uji Coba Push Notifikasi SIMS',
            'Halo ' . $user->name . ', push notifikasi Firebase & alert berhasil terhubung!',
            ['type' => 'test']
        );

        return response()->json([
            'success' => true,
            'message' => 'Test push notification sent!',
        ]);
    }
}
