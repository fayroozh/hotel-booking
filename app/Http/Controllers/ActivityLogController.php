<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    // عرض سجلات النشاط (للمدير فقط)
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $logs = Activity::with('causer', 'subject')->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($logs);
    }

    // عرض نشاطاتي (للمستخدم العادي)
    public function myActivity()
    {
        $logs = Activity::where('causer_id', Auth::id())
                        ->with('subject')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);
        
        return response()->json($logs);
    }
}
