<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Gallery;
use App\Models\SchoolEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HumasController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $siswa->load('schoolClass');

        $upcomingEvents = SchoolEvent::with(['gallery.photos' => fn($q) => $q->orderBy('sort_order')->limit(4)])
            ->where('is_published', true)
            ->where('event_date', '>=', today()->subDay())
            ->orderBy('event_date')
            ->limit(10)
            ->get();

        $latestGalleries = Gallery::where('is_published', true)
            ->withCount('photos')
            ->latest()
            ->limit(6)
            ->get();

        $latestAnnouncements = Announcement::published()
            ->forRole('siswa', $siswa->class_id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('siswa.humas.index', compact('siswa', 'upcomingEvents', 'latestGalleries', 'latestAnnouncements'));
    }

    public function eventShow(SchoolEvent $event): View|RedirectResponse
    {
        abort_if(! $event->is_published, 404);

        $event->load(['gallery.photos' => fn($q) => $q->orderBy('sort_order')]);

        // Other upcoming events (exclude current)
        $otherEvents = SchoolEvent::where('is_published', true)
            ->where('id', '!=', $event->id)
            ->where('event_date', '>=', today()->subDay())
            ->orderBy('event_date')
            ->limit(4)
            ->get();

        return view('siswa.humas.event-show', compact('event', 'otherEvents'));
    }

    public function galleryIndex(): View
    {
        $galleries = Gallery::where('is_published', true)
            ->withCount('photos')
            ->latest()
            ->paginate(12);

        return view('siswa.humas.gallery.index', compact('galleries'));
    }

    public function galleryShow(Gallery $gallery): View
    {
        abort_if(! $gallery->is_published, 404);

        $photos = $gallery->photos()->orderBy('sort_order')->get();

        return view('siswa.humas.gallery.show', compact('gallery', 'photos'));
    }

    public function announcementsIndex(): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        $announcements = Announcement::published()
            ->forRole('siswa', $siswa->class_id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('siswa.humas.announcements.index', compact('announcements'));
    }

    public function announcementShow(Announcement $announcement): View
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        abort_if(! $announcement->isPublished(), 404);

        $otherAnnouncements = Announcement::published()
            ->forRole('siswa', $siswa->class_id)
            ->where('id', '!=', $announcement->id)
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('siswa.humas.announcements.show', compact('announcement', 'otherAnnouncements'));
    }
}
