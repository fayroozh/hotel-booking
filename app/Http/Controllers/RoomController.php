<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    // جلب كل الغرف (Admin فقط)
    public function index()
    {
        if(Auth::user()->role != 'admin'){
            return response()->json(['message'=>'غير مسموح'], 403);
        }

        $rooms = Room::with('hotel')->get();
        return response()->json($rooms);
    }

    // جلب غرف فندق معيّن
    public function roomsByHotel($hotel_id)
    {
        $hotel = Hotel::findOrFail($hotel_id);
        $rooms = $hotel->rooms()->get();
        return response()->json($rooms);
    }

    // إنشاء غرفة جديدة (Admin أو Manager إذا للفندق نفسه)
    public function store(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|integer',
            'type' => 'required|string',
            'price' => 'required|numeric',
            'features' => 'nullable|array'
        ]);

        $hotel = Hotel::findOrFail($request->hotel_id);

        if(Auth::user()->role == 'manager' && $hotel->manager_id != Auth::id()){
            return response()->json(['message'=>'غير مسموح'], 403);
        }

        $room = Room::create([
            'hotel_id' => $request->hotel_id,
            'type' => $request->type,
            'price' => $request->price,
            'features' => json_encode($request->features),
        ]);

        return response()->json($room, 201);
    }

    // عرض بيانات غرفة واحدة
    public function show($id)
    {
        $room = Room::with('hotel')->findOrFail($id);
        return response()->json($room);
    }

    // تحديث الغرفة
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $hotel = $room->hotel;

        if(Auth::user()->role == 'manager' && $hotel->manager_id != Auth::id()){
            return response()->json(['message'=>'غير مسموح'], 403);
        }

        $room->update($request->only('type','price','features'));
        return response()->json($room);
    }

    // حذف الغرفة
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $hotel = $room->hotel;

        if(Auth::user()->role == 'manager' && $hotel->manager_id != Auth::id()){
            return response()->json(['message'=>'غير مسموح'], 403);
        }

        $room->delete();
        return response()->json(['message'=>'تم حذف الغرفة']);
    }
}
