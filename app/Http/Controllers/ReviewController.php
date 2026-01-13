<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{

    // عرض كل التقييمات لفندق معين
    public function index($hotel_id)
    {
        $hotel = Hotel::findOrFail($hotel_id);

        $reviews = $hotel->reviews()->with('user')->get();

        return response()->json($reviews);
    }

    // إضافة تقييم جديد
    public function store(Request $request, $hotel_id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $hotel = Hotel::findOrFail($hotel_id);

        $review = Review::create([
            'user_id' => Auth::id(),
            'hotel_id' => $hotel->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json($review, 201);
    }

    // تحديث تقييم مستخدم
    public function update(Request $request, $hotel_id, $id)
    {
        $review = Review::findOrFail($id);

        if (Auth::id() !== $review->user_id && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $review->update($request->only('rating', 'comment'));

        return response()->json($review);
    }

    // حذف تقييم
    public function destroy($hotel_id, $id)
    {
        $review = Review::findOrFail($id);

        if (Auth::id() !== $review->user_id && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'تم حذف التقييم']);
    }
}
