<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $allNotifications = Auth::user()->notifications()->latest()->get();
        
        $notifications = [
            'financial' => $allNotifications->where('type', 'financial')->map(function($n) {
                return [
                    'title' => $n->title,
                    'time' => $n->created_at->diffForHumans(),
                    'amount' => str_contains($n->message, 'Rp') ? explode(' ', $n->message)[0] : '', // Simple heuristic
                    'category' => $n->link ?? 'Umum'
                ];
            }),
            'bills' => $allNotifications->where('type', 'bill')->map(function($n) {
                return [
                    'title' => $n->title,
                    'status' => 'Belum Bayar',
                    'status_type' => 'warning',
                    'icon' => 'event_busy',
                    'detail' => $n->message
                ];
            }),
            'tasks' => $allNotifications->where('type', 'task')->map(function($n) {
                return [
                    'user' => 'Family Member',
                    'avatar' => 'https://ui-avatars.com/api/?name=User',
                    'task' => $n->title,
                    'time' => $n->created_at->diffForHumans(),
                    'points' => 10
                ];
            }),
        ];

        return view('notification.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notifikasi dihapus.');
    }
}
