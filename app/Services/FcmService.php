<?php

namespace App\Services;

use App\Models\UserFcmToken;
use Illuminate\Support\Facades\Cache;
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
            Log::info("[FcmService] No FCM tokens found in database for users: " . implode(',', $userIds));
            return false;
        }

        return static::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send Push Notification to raw list of FCM Tokens.
     */
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        // 1. Try FCM HTTP v1 API first (Google's official requirement since June 2024)
        $credentialsPath = config('services.firebase.credentials') 
            ?: env('FIREBASE_CREDENTIALS') 
            ?: storage_path('firebase/service-account.json');

        if (!file_exists($credentialsPath)) {
            $firebaseFiles = glob(storage_path('firebase/*.json'));
            if (!empty($firebaseFiles)) {
                $credentialsPath = $firebaseFiles[0];
            }
        }

        if (file_exists($credentialsPath)) {
            return static::sendViaHttpV1($credentialsPath, $tokens, $title, $body, $data);
        }

        // 2. Fallback to Legacy Server Key if configured
        $serverKey = config('services.firebase.server_key') ?: env('FCM_SERVER_KEY');
        if (!empty($serverKey)) {
            return static::sendViaLegacy($serverKey, $tokens, $title, $body, $data);
        }

        Log::warning("[FcmService] Neither Firebase Service Account (FIREBASE_CREDENTIALS) nor FCM_SERVER_KEY is configured.");
        return false;
    }

    /**
     * Send via modern FCM HTTP v1 API.
     */
    protected static function sendViaHttpV1(string $credentialsPath, array $tokens, string $title, string $body, array $data = []): bool
    {
        try {
            $json = json_decode(file_get_contents($credentialsPath), true);
            if (!$json || empty($json['project_id']) || empty($json['private_key']) || empty($json['client_email'])) {
                Log::error("[FcmService] Invalid Firebase service account JSON in: " . $credentialsPath);
                return false;
            }

            $projectId = $json['project_id'];
            $accessToken = static::getGoogleAccessToken($json);
            if (!$accessToken) {
                Log::error("[FcmService] Failed to obtain Google OAuth2 access token.");
                return false;
            }

            $successCount = 0;
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // FCM v1 sends per token
            foreach ($tokens as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'android' => [
                            'priority' => 'HIGH',
                            'notification' => [
                                'channel_id'              => 'sims_alert_v3_channel',
                                'sound'                   => 'default',
                                'default_sound'           => true,
                                'default_vibrate_timings' => true,
                                'notification_priority'   => 'PRIORITY_MAX',
                            ],
                        ],
                        'data' => array_map('strval', array_merge($data, [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ])),
                    ],
                ];

                $response = Http::withToken($accessToken)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $errBody = $response->body();
                    Log::warning("[FcmService] HTTP v1 Send Failed for token: " . substr($token, 0, 15) . "... Response: " . $errBody);
                    // Remove dead token if unregistered
                    if (str_contains($errBody, 'UNREGISTERED') || str_contains($errBody, 'NOT_FOUND')) {
                        UserFcmToken::where('fcm_token', $token)->delete();
                    }
                }
            }

            Log::info("[FcmService] Sent {$successCount}/" . count($tokens) . " notifications via FCM HTTP v1.");
            return $successCount > 0;
        } catch (\Throwable $e) {
            Log::error("[FcmService] Exception in HTTP v1: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Google OAuth2 Access Token using Service Account JWT.
     */
    protected static function getGoogleAccessToken(array $serviceAccount): ?string
    {
        return Cache::remember('fcm_google_access_token', 3300, function () use ($serviceAccount) {
            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claims = json_encode([
                'iss'   => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]);

            $base64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
            $base64Claims = rtrim(strtr(base64_encode($claims), '+/', '-_'), '=');
            $dataToSign = $base64Header . '.' . $base64Claims;

            $signature = '';
            $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
            if (!$privateKey) {
                Log::error("[FcmService] Unable to parse private key from service account.");
                return null;
            }

            if (!openssl_sign($dataToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                Log::error("[FcmService] Failed to sign JWT with openssl.");
                return null;
            }

            $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            $jwt = $dataToSign . '.' . $base64Signature;

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error("[FcmService] OAuth2 Token Error: " . $response->body());
            return null;
        });
    }

    /**
     * Fallback to Legacy FCM Endpoint.
     */
    protected static function sendViaLegacy(string $serverKey, array $tokens, string $title, string $body, array $data = []): bool
    {
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
                'android_channel_id' => 'sims_alert_v3_channel',
                'channel_id'         => 'sims_alert_v3_channel',
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id'              => 'sims_alert_v3_channel',
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
                Log::info("[FcmService] Legacy FCM sent successfully to " . count($tokens) . " tokens.");
                return true;
            }

            Log::error("[FcmService] Legacy FCM Response Error: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("[FcmService] Legacy FCM Exception: " . $e->getMessage());
            return false;
        }
    }
}
