<?php

namespace App\Services;

use App\Models\UserFcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Kirim push notification ke spesifik user ID.
     */
    public static function sendToUser(
        int     $userId,
        string  $title,
        string  $body = '',
        array   $data = []
    ): void {
        try {
            $tokens = UserFcmToken::where('user_id', $userId)->pluck('fcm_token')->toArray();
            if (empty($tokens)) {
                return;
            }

            static::sendToTokens($tokens, $title, $body, $data);
        } catch (\Throwable $e) {
            Log::error('FCM sendToUser error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim push notification ke daftar user ID.
     */
    public static function sendToUsers(
        array   $userIds,
        string  $title,
        string  $body = '',
        array   $data = []
    ): void {
        if (empty($userIds)) return;

        try {
            $tokens = UserFcmToken::whereIn('user_id', $userIds)->pluck('fcm_token')->toArray();
            if (empty($tokens)) {
                return;
            }

            static::sendToTokens($tokens, $title, $body, $data);
        } catch (\Throwable $e) {
            Log::error('FCM sendToUsers error: ' . $e->getMessage());
        }
    }

    /**
     * Dispatche payload notifikasi FCM berprioritas tinggi.
     */
    public static function sendToTokens(
        array  $tokens,
        string $title,
        string $body = '',
        array  $data = []
    ): void {
        $serverKey = config('services.fcm.server_key') ?? env('FCM_SERVER_KEY');

        // Filter token unik & valid
        $tokens = array_unique(array_filter($tokens));
        if (empty($tokens)) return;

        foreach (array_chunk($tokens, 500) as $chunk) {
            if ($serverKey) {
                try {
                    Http::withHeaders([
                        'Authorization' => 'key=' . $serverKey,
                        'Content-Type'  => 'application/json',
                    ])->post('https://fcm.googleapis.com/fcm/send', [
                        'registration_ids' => array_values($chunk),
                        'priority' => 'high',
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                            'sound' => 'default',
                            'android_channel_id' => 'high_importance_channel',
                        ],
                        'data' => array_merge($data, [
                            'title' => $title,
                            'body'  => $body,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ]),
                    ]);
                } catch (\Throwable $e) {
                    Log::error('FCM Send Exception: ' . $e->getMessage());
                }
            } else {
                Log::info("FCM Push [STUB]: Title='$title', Tokens=" . count($chunk));
            }
        }
    }
}
