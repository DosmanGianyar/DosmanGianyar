<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AppNotification::forUser(Auth::id())
            ->latest()
            ->paginate(20);

        // Mark all as read when opening the list
        AppNotification::forUser(Auth::id())->unread()->update(['read_at' => now()]);

        return view('siswa.notifications.index', compact('notifications'));
    }

    public function markRead(AppNotification $notification): RedirectResponse
    {
        abort_if($notification->user_id !== Auth::id(), 403);
        $notification->update(['read_at' => now()]);
        return redirect($notification->url ?? route('siswa.notifications'));
    }

    public function markAllRead(): RedirectResponse
    {
        AppNotification::forUser(Auth::id())->unread()->update(['read_at' => now()]);
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
