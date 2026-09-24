<?php

namespace App\Http\Controllers;

use App\Models\SpiNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications count and latest items (for polling).
     */
    public function unread(): JsonResponse
    {
        $user  = Auth::user();
        $count = $user->notifications()->where('is_read', false)->count();

        $notifications = $user->notifications()
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'       => $n->id,
                'title'    => $n->title,
                'message'  => $n->message,
                'type'     => $n->type,
                'link'     => $n->link,
                'is_read'  => $n->is_read,
                'time_ago' => $n->time_ago,
            ]);

        return response()->json([
            'count'         => $count,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, SpiNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === Auth::id(), 403);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        Auth::user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
