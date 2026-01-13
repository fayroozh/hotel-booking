<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // عرض الإشعارات
    public function index()
    {
        return response()->json(Auth::user()->notifications);
    }

    // عرض الإشعارات غير المقروءة فقط
    public function unread()
    {
        return response()->json(Auth::user()->unreadNotifications);
    }

    // تحديد إشعار كمقروء
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['message' => 'تم تحديد الإشعار كمقروء']);
    }

    // تحديد الكل كمقروء
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'تم تحديد كل الإشعارات كمقروءة']);
    }
}
