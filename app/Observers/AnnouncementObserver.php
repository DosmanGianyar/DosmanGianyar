<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Services\NotificationService;

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
        $title = 'Pengumuman Baru';
        $body  = strlen($announcement->title) > 80
            ? substr($announcement->title, 0, 77) . '...'
            : $announcement->title;

        NotificationService::broadcastToTarget(
            targetRole: $announcement->target ?? 'all',
            targetClassIds: $announcement->target_class_ids,
            title: $title,
            body: $body,
            type: 'info',
            url: 'announcement/' . $announcement->id
        );
    }
}
