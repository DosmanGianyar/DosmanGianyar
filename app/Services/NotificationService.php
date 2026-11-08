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

    /**
     * Kirim notifikasi secara massal ke daftar user_id (bulk insert + batch FCM).
     */
    public static function sendToMany(
        array   $userIds,
        string  $title,
        string  $body     = '',
        string  $type     = 'info',
        ?string $url      = null,
        ?string $imageUrl = null,
    ): void {
        $userIds = array_values(array_unique(array_filter($userIds)));
        if (empty($userIds)) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($userIds as $uid) {
            $rows[] = [
                'user_id'    => $uid,
                'title'      => $title,
                'body'       => $body,
                'type'       => $type,
                'url'        => $url,
                'image_url'  => $imageUrl,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            AppNotification::insert($chunk);
        }

        FcmService::sendToUsers($userIds, $title, $body, [
            'type' => $type,
            'url'  => $url ?? '',
        ]);
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
        $userIds = User::whereIn('role', $roles)->pluck('id')->toArray();
        static::sendToMany($userIds, $title, $body, $type, $url, $imageUrl);
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
            $query->whereIn('role', ['siswa', 'pengelola']);
            if (!empty($targetClassIds)) {
                $query->whereIn('class_id', $targetClassIds);
            }
        } elseif ($targetRole === 'orangtua') {
            $query->where('role', 'orangtua');
            if (!empty($targetClassIds)) {
                // Filter orang tua dari siswa yang berada di kelas target
                $studentUserIds = User::whereIn('role', ['siswa', 'pengelola'])->whereIn('class_id', $targetClassIds)->pluck('id');
                $parentUserIds  = \DB::table('parent_students')
                    ->whereIn('student_id', $studentUserIds)
                    ->pluck('parent_id');
                $query->whereIn('id', $parentUserIds);
            }
        } else {
            // 'all' -> Semua peran (Guru, Siswa, Orang Tua)
            if (!empty($targetClassIds)) {
                $studentUserIds = User::whereIn('role', ['siswa', 'pengelola'])->whereIn('class_id', $targetClassIds)->pluck('id');
                $parentUserIds  = \DB::table('parent_students')
                    ->whereIn('student_id', $studentUserIds)
                    ->pluck('parent_id');

                $query->where(function ($q) use ($targetClassIds, $parentUserIds) {
                    $q->where('role', 'guru')
                      ->orWhere(fn ($sq) => $sq->whereIn('role', ['siswa', 'pengelola'])->whereIn('class_id', $targetClassIds))
                      ->orWhereIn('id', $parentUserIds);
                });
            } else {
                $query->whereIn('role', ['guru', 'pegawai', 'siswa', 'pengelola', 'orangtua']);
            }
        }

        $userIds = $query->pluck('id')->toArray();
        static::sendToMany($userIds, $title, $body, $type, $url, $imageUrl);
    }

    /**
     * Broadcast notifikasi saat Admin membuat Hari Libur atau Hari Sekolah Khusus.
     */
    public static function broadcastHoliday(\App\Models\Holiday $holiday): void
    {
        $dateFormatted = $holiday->date
            ? \Illuminate\Support\Carbon::parse($holiday->date)->locale('id')->translatedFormat('l, d F Y')
            : '';

        $isLibur = $holiday->type === 'libur';
        $title = $isLibur
            ? "📅 Informasi Hari Libur: {$holiday->description}"
            : "🏫 Informasi Hari Sekolah Khusus: {$holiday->description}";

        $classIds = null;
        $scopeLabel = 'seluruh kelas';

        if ($holiday->applies_to === 'kelas_tertentu') {
            $classes = $holiday->schoolClasses()->get(['classes.id', 'classes.name']);
            $classIds = $classes->pluck('id')->toArray();
            if ($classes->isNotEmpty()) {
                $scopeLabel = 'Kelas ' . $classes->pluck('name')->implode(', ');
            }
        }

        $body = $isLibur
            ? "Diberitahukan bahwa pada {$dateFormatted} ditetapkan sebagai Hari Libur ({$holiday->description}) untuk {$scopeLabel}. Presensi siswa tidak diperlukan pada tanggal tersebut."
            : "Diberitahukan bahwa pada {$dateFormatted} ditetapkan sebagai Hari Sekolah Khusus ({$holiday->description}) untuk {$scopeLabel}. Siswa wajib melakukan presensi kehadiran seperti biasa.";

        static::broadcastToTarget(
            'all',
            !empty($classIds) ? $classIds : null,
            $title,
            $body,
            'info'
        );
    }
}
