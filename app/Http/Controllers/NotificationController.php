<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->wantsJson()) {
            $notifications = $user->appNotifications()->take(10)->get();
            $unreadCount = $user->unreadNotificationsCount();

            return response()->json([
                'unread_count' => $unreadCount,
                'notifications' => $notifications,
            ]);
        }

        $notifications = $user->appNotifications()->paginate(20);

        return view('dashboard.notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = AppNotification::where('user_id', Auth::id())->findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi telah ditandai dibaca.',
                'unread_count' => Auth::user()->unreadNotificationsCount(),
            ]);
        }

        if ($notification->url) {
            $destUrl = $this->normalizeNotificationUrl($notification->url);

            return redirect($destUrl);
        }

        return back()->with('success', 'Notifikasi telah ditandai dibaca.');
    }

    public function markAllAsRead(Request $request)
    {
        AppNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi telah ditandai dibaca.',
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    protected function normalizeNotificationUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parsed = parse_url($url);
        if (isset($parsed['host']) && $parsed['host'] === 'localhost' && isset($parsed['port']) && (int) $parsed['port'] === 8000) {
            $path = $parsed['path'] ?? '/';

            return url($path);
        }

        return $url;
    }
}
