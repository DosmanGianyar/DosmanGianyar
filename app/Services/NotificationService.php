<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    public static function send(
        int     $userId,
        string  $title,
        string  $body     = '',
        string  $type     = 'info',
        ?string $url      = null,
        ?string $imageUrl = null,
    ): AppNotification {
        return AppNotification::create([
            'user_id'   => $userId,
            'title'     => $title,
            'body'      => $body,
            'type'      => $type,
            'url'       => $url,
            'image_url' => $imageUrl,
        ]);
    }

    /**
     * Kirim notifikasi secara khusus kepada semua akun orangtua yang terhubung dengan siswa.
     */
    public static function notifyParentsOfStudent(
        User    $student,
        string  $title,
        string  $body     = '',
        string  $type     = 'info',
        ?string $url      = null,
        ?string $imageUrl = null,
    ): void {
        $student->loadMissing('parentAccounts');
        foreach ($student->parentAccounts as $parent) {
            static::send($parent->id, $title, $body, $type, $url, $imageUrl);
        }
    }

    /** Send to all users matching the given roles. */
    public static function broadcastToRole(
        array   $roles,
        string  $title,
        string  $body     = '',
        string  $type     = 'info',
        ?string $url      = null,
        ?string $imageUrl = null,
    ): void {
        User::whereIn('role', $roles)->pluck('id')->each(
            fn(int $id) => static::send($id, $title, $body, $type, $url, $imageUrl)
        );
    }
}
