<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelController extends Controller
{
    // عرض كل الفنادق
    public function index()
    {
        $hotels = Hotel::with('manager')->get();
        return response()->json($hotels);
    }

    // إنشاء فندق جديد (Admin فقط)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'stars' => 'required|integer',
            'description' => 'nullable|string'
        ]);

        // لو المستخدم Admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $hotel = Hotel::create([
            'name' => $request->name,
            'location' => $request->location,
            'stars' => $request->stars,
            'description' => $request->description,
            'status' => 'pending', // افتراضي
        ]);

        return response()->json($hotel, 201);
    }

    // عرض فندق واحد
    public function show($id)
    {
        $hotel = Hotel::with('rooms', 'reviews')->findOrFail($id);
        return response()->json($hotel);
    }

    // تحديث بيانات فندق
    public function update(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);

        // لو Admin أو Manager يخصه
        if (
            Auth::user()->role !== 'admin' &&
            !(Auth::user()->role === 'manager' && $hotel->manager_id == Auth::id())
        ) {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $hotel->update($request->only('name', 'location', 'stars', 'description', 'status'));
        return response()->json($hotel);
    }

    // حذف فندق
    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);

        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $hotel->delete();
        return response()->json(['message' => 'تم الحذف']);
    }
    public function search(Request $request)
    {
        $query = Hotel::query();

        // بحث نصي في الاسم
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // فلترة حسب المدينة
        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        // فلترة حسب النجوم
        if ($request->has('stars')) {
            $query->where('stars', $request->stars);
        }

        // يمكنك إضافة المزيد من الفلاتر بسهولة

        $hotels = $query->get();

        return response()->json($hotels);
    }

}


