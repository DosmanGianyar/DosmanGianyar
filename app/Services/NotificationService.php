<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    public static function send(
        int     $userId,
        string  $title,
        string  $body  = '',
        string  $type  = 'info',
        ?string $url   = null,
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'url'     => $url,
        ]);
    }

    /** Send to all users matching the given roles. */
    public static function broadcastToRole(
        array  $roles,
        string $title,
        string $body  = '',
        string $type  = 'info',
        string $url   = null,
    ): void {
        User::whereIn('role', $roles)->pluck('id')->each(
            fn(int $id) => static::send($id, $title, $body, $type, $url)
        );
    }
}
