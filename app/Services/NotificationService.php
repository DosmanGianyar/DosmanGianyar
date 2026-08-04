<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\SchoolClass;
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
        $notification = AppNotification::create([
            'user_id'   => $userId,
            'title'     => $title,
            'body'      => $body,
            'type'      => $type,
            'url'       => $url,
            'image_url' => $imageUrl,
        ]);

        // Push ke FCM Token perangkat user
        FcmService::sendToUser($userId, $title, $body, [
            'type' => $type,
            'url'  => $url ?? '',
        ]);

        return $notification;
    }

    /**
     * Kirim notifikasi ke Wali Kelas dari siswa terkait.
     */
    public static function notifyHomeroomTeacher(
        User    $student,
        string  $title,
        string  $body     = '',
        string  $type     = 'info',
        ?string $url      = null,
        ?string $imageUrl = null,
    ): void {
        if (!$student->class_id) return;

        $homeroomClass = SchoolClass::find($student->class_id);
        if ($homeroomClass && $homeroomClass->homeroom_teacher_id) {
            static::send($homeroomClass->homeroom_teacher_id, $title, $body, $type, $url, $imageUrl);
        }
    }

    /**
     * Kirim notifikasi kepada semua akun orangtua yang terhubung dengan siswa.
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

    /** Broadcast ke semua user dengan peran tertentu. */
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

    /**
     * Broadcast pengumuman Humas ke peran target & pilihan kelas spesifik.
     */
    public static function broadcastToTarget(
        string  $targetRole,       // 'all' | 'guru' | 'siswa' | 'orangtua'
        ?array  $targetClassIds,   // Array ID kelas atau null/kosong untuk semua kelas
        string  $title,
        string  $body     = '',
        string  $type     = 'info',
        ?string $url      = null,
        ?string $imageUrl = null,
    ): void {
        $query = User::query();

        if ($targetRole === 'guru') {
            $query->where('role', 'guru');
        } elseif ($targetRole === 'siswa') {
            $query->where('role', 'siswa');
            if (!empty($targetClassIds)) {
                $query->whereIn('class_id', $targetClassIds);
            }
        } elseif ($targetRole === 'orangtua') {
            $query->where('role', 'orangtua');
            if (!empty($targetClassIds)) {
                // Filter orang tua dari siswa yang berada di kelas target
                $studentUserIds = User::where('role', 'siswa')->whereIn('class_id', $targetClassIds)->pluck('id');
                $parentUserIds  = \DB::table('parent_students')
                    ->whereIn('student_id', $studentUserIds)
                    ->pluck('parent_id');
                $query->whereIn('id', $parentUserIds);
            }
        } else {
            // 'all' -> Semua peran
            if (!empty($targetClassIds)) {
                $query->where(function ($q) use ($targetClassIds) {
                    $q->where('role', 'guru')
                      ->orWhere(fn($sq) => $sq->where('role', 'siswa')->whereIn('class_id', $targetClassIds));
                });
            }
        }

        $userIds = $query->pluck('id')->toArray();
        foreach ($userIds as $id) {
            static::send($id, $title, $body, $type, $url, $imageUrl);
        }
    }
}
