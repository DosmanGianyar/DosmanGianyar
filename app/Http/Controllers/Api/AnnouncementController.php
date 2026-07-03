<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Pengumuman terbaru (5 item) untuk widget beranda.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $announcements = Announcement::published()
            ->forRole($user->role)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(5)
            ->with('author:id,name')
            ->get(['id', 'title', 'body', 'is_pinned', 'published_at', 'author_id']);

        return response()->json([
            'announcements' => $announcements->map(fn ($a) => $this->format($a)),
        ]);
    }

    /**
     * Daftar lengkap pengumuman dengan paginasi (cursor-based untuk infinite scroll).
     */
    public function all(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $paginator = Announcement::published()
            ->forRole($user->role)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->with('author:id,name')
            ->paginate(20, ['id', 'title', 'body', 'is_pinned', 'published_at', 'author_id']);

        return response()->json([
            'announcements' => collect($paginator->items())->map(fn ($a) => $this->format($a)),
            'has_more'      => $paginator->hasMorePages(),
            'next_page'     => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ]);
    }

    /**
     * Detail satu pengumuman.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $announcement = Announcement::published()
            ->forRole($user->role)
            ->with('author:id,name')
            ->findOrFail($id);

        return response()->json(['announcement' => $this->format($announcement)]);
    }

    private function format(Announcement $a): array
    {
        return [
            'id'           => $a->id,
            'title'        => $a->title,
            'body'         => $a->body,
            'is_pinned'    => $a->is_pinned,
            'published_at' => $a->published_at->toIso8601String(),
            'author_name'  => $a->author?->name,
        ];
    }
}
