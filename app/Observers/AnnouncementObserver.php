<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnnouncementObserver
{
    /**
     * Saat pengumuman dibuat langsung dengan published_at.
     */
    public function created(Announcement $announcement): void
    {
        if ($announcement->published_at !== null) {
            $this->notify($announcement);
        }
    }

    /**
     * Saat pengumuman di-update: hanya kirim notifikasi jika
     * published_at baru saja di-set (sebelumnya null = draft → publish).
     */
    public function updated(Announcement $announcement): void
    {
        if (
            $announcement->wasChanged('published_at') &&
            $announcement->published_at !== null &&
            $announcement->getOriginal('published_at') === null
        ) {
            $this->notify($announcement);
        }
    }

    private function notify(Announcement $announcement): void
    {
        $roles = match ($announcement->target) {
            'siswa' => ['siswa', 'pengelola'],
            'guru'  => ['guru'],
            default => ['siswa', 'pengelola', 'guru'],
        };

        $now  = now()->toDateTimeString();
        $body = strlen($announcement->title) > 80
            ? substr($announcement->title, 0, 77) . '...'
            : $announcement->title;

        // Chunk 200 agar tidak OOM untuk sekolah besar
        User::whereIn('role', $roles)
            ->select('id')
            ->chunkById(200, function ($users) use ($announcement, $body, $now) {
                DB::table('app_notifications')->insert(
                    $users->map(fn ($u) => [
                        'user_id'    => $u->id,
                        'title'      => 'Pengumuman Baru',
                        'body'       => $body,
                        'type'       => 'info',
                        'url'        => 'announcement/' . $announcement->id,
                        'read_at'    => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            });
    }
}
