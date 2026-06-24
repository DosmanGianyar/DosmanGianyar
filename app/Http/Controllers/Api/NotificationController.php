<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Daftar notifikasi user (20 terbaru), unread duluan.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notifications = AppNotification::forUser($user->id)
            ->orderByRaw('read_at IS NULL DESC')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'title', 'body', 'type', 'url', 'read_at', 'created_at']);

        return response()->json([
            'notifications' => $notifications->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'body'       => $n->body,
                'type'       => $n->type,
                'url'        => $n->url,
                'is_read'    => $n->isRead(),
                'created_at' => $n->created_at->toIso8601String(),
            ]),
            'unread_count' => AppNotification::forUser($user->id)->unread()->count(),
        ]);
    }

    /**
     * Jumlah notifikasi belum dibaca (untuk badge).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'unread_count' => AppNotification::forUser($user->id)->unread()->count(),
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notification = AppNotification::forUser($user->id)->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca.']);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        AppNotification::forUser($user->id)->unread()->update(['read_at' => now()]);

        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca.']);
    }
}
