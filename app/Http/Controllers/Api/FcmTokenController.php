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

        $sent = FcmService::sendToUsers(
            [$user->id],
            'Test Push Notification SIMS',
            'Halo ' . $user->name . ', push notifikasi Firebase berhasil terhubung!',
            ['type' => 'test']
        );

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Test push notification sent!' : 'Failed to send notification. Check FCM_SERVER_KEY in .env.',
        ]);
    }
}
