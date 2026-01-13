<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WalletController;
use App\Notifications\BookingStatusUpdated;

class BookingController extends Controller
{
    // عرض كل الحجوزات (Admin فقط)
    public function index()
    {
        if (Auth::user()->role != 'admin') {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $bookings = Booking::with('user', 'room.hotel')->orderBy('created_at', 'desc')->get();
        return response()->json($bookings);
    }

    // جلب حجوزات المستخدم نفسه
    public function myBookings()
    {
        $bookings = Booking::with('room.hotel')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($bookings);
    }

    // عرض حجز محدد
    public function show($id)
    {
        $booking = Booking::with('room.hotel', 'user')->findOrFail($id);

        if (Auth::user()->role !== 'admin' && Auth::id() !== $booking->user_id) {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        return response()->json($booking);
    }

    // تحقق من توفّر الغرفة للتواريخ المحددة
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $isAvailable = $this->isRoomAvailable($request->room_id, $request->start_date, $request->end_date);

        return response()->json([
            'available' => $isAvailable
        ]);
    }

    // دالة مساعدة للتحقق من التوفر
    private function isRoomAvailable($roomId, $startDate, $endDate)
    {
        return !Booking::where('room_id', $roomId)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }

    // إنشاء حجز جديد
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date'
        ]);

        $room = Room::findOrFail($request->room_id);

        // 1. تحقق من التوافر
        if (!$this->isRoomAvailable($room->id, $request->start_date, $request->end_date)) {
            return response()->json(['message' => 'الغرفة غير متاحة في هذه التواريخ'], 400);
        }

        // 2. حساب السعر
        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);
        $days = $start->diffInDays($end);
        if ($days == 0) $days = 1; // على الأقل يوم واحد
        
        $totalPrice = $room->price * $days;

        // 3. التحقق من الرصيد والخصم (إذا كان الدفع بالمحفظة)
        // سنفترض الدفع دائماً بالمحفظة للتسهيل كما طلب المستخدم (Backend API)
        $user = Auth::user();
        if ($user->wallet_balance < $totalPrice) {
            return response()->json(['message' => 'رصيد المحفظة غير كافٍ'], 400);
        }

        // خصم المبلغ
        $user->deductFromWallet($totalPrice, "حجز غرفة {$room->type} لمدة $days أيام");

        // 4. إنشاء الحجز
        $booking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'confirmed',
            'total_price' => $totalPrice,
        ]);

        // إشعار المستخدم
        $user->notify(new BookingStatusUpdated($booking));

        return response()->json([
            'message' => 'تم الحجز بنجاح',
            'booking' => $booking,
            'new_balance' => $user->wallet_balance
        ], 201);
    }

    // تحديث حالة الحجز (Admin)
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled'
        ]);

        $oldStatus = $booking->status;
        $booking->status = $request->status;
        $booking->save();

        if ($oldStatus !== $request->status) {
            // إرسال إشعار للمستخدم صاحب الحجز
            $booking->user->notify(new BookingStatusUpdated($booking));
            
            // لو تم الإلغاء، يمكننا إعادة المبلغ (خيار إضافي)
            if ($request->status === 'cancelled' && $oldStatus === 'confirmed') {
                 $booking->user->addToWallet($booking->total_price, 'refund', "استرجاع مبلغ حجز #{$booking->id}");
            }
        }

        return response()->json($booking);
    }
}
