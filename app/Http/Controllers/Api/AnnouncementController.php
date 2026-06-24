<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Pengumuman terbaru yang relevan untuk role user yang login.
     * Dipin tampil di atas, sisanya diurutkan terbaru.
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
            'announcements' => $announcements->map(fn ($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'body'         => $a->body,
                'is_pinned'    => $a->is_pinned,
                'published_at' => $a->published_at->toIso8601String(),
                'author_name'  => $a->author?->name,
            ]),
        ]);
    }
}
