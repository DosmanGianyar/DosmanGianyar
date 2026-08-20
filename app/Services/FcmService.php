<?php

namespace App\Services;

use App\Models\UserFcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send Push Notification to single user ID.
     */
    public static function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        return static::sendToUsers([$userId], $title, $body, $data);
    }

    /**
     * Send Push Notification to specific user IDs.
     */
    public static function sendToUsers(array $userIds, string $title, string $body, array $data = []): bool
    {
        $tokens = UserFcmToken::whereIn('user_id', $userIds)
            ->pluck('fcm_token')
            ->filter()
            ->unique()
            ->toArray();

        if (empty($tokens)) {
            Log::info("[FcmService] No FCM tokens found for users: " . implode(',', $userIds));
            return false;
        }

        return static::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send Push Notification to raw list of FCM Tokens.
     */
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        $serverKey = config('services.firebase.server_key');

        if (empty($serverKey)) {
            Log::warning("[FcmService] Firebase Server Key is not configured in .env (FCM_SERVER_KEY).");
            return false;
        }

        $headers = [
            'Authorization' => 'key=' . $serverKey,
            'Content-Type'  => 'application/json',
        ];

        $payload = [
            'registration_ids' => array_values($tokens),
            'notification'     => [
                'title'              => $title,
                'body'               => $body,
                'sound'              => 'default',
                'android_channel_id' => 'sims_high_importance_channel',
                'channel_id'         => 'sims_high_importance_channel',
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id'              => 'sims_high_importance_channel',
                    'sound'                   => 'default',
                    'default_sound'           => true,
                    'default_vibrate_timings' => true,
                    'notification_priority'   => 'PRIORITY_MAX',
                ],
            ],
            'data' => array_merge($data, [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]),
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders($headers)
                ->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                Log::info("[FcmService] FCM Notification sent successfully to " . count($tokens) . " tokens.");
                return true;
            }

            Log::error("[FcmService] FCM Response Error: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("[FcmService] Exception sending FCM: " . $e->getMessage());
            return false;
        }
    }
}
