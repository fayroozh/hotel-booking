<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\Auth\GoogleAuthController;

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

use App\Http\Controllers\HotelController;

Route::middleware('auth:sanctum')->group(function () {

    // يجب أن يكون البحث قبل id لتجنب التعارض
    Route::get('/hotels/search', [HotelController::class, 'search']);
    
    Route::get('/hotels', [HotelController::class, 'index']);
    Route::post('/hotels', [HotelController::class, 'store']);
    Route::get('/hotels/{id}', [HotelController::class, 'show']);
    Route::put('/hotels/{id}', [HotelController::class, 'update']);
    Route::delete('/hotels/{id}', [HotelController::class, 'destroy']);

});

use App\Http\Controllers\RoomController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/rooms', [RoomController::class, 'index']); // Admin only
    Route::get('/rooms/{id}', [RoomController::class, 'show']);
    Route::get('/hotels/{hotel_id}/rooms', [RoomController::class, 'roomsByHotel']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms/{id}', [RoomController::class, 'update']);
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);

});

use App\Http\Controllers\BookingController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/bookings', [BookingController::class, 'index']); // Admin
    Route::get('/bookings/my', [BookingController::class, 'myBookings']); // User
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::post('/bookings/check', [BookingController::class, 'checkAvailability']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']); // Admin status update

});

use App\Http\Controllers\WalletController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);

    // شحن المحفظة (Admin / Manager)
    Route::post('/wallet/credit/{userId}', [WalletController::class, 'credit']);

});

use App\Http\Controllers\ReviewController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/hotels/{hotel_id}/reviews', [ReviewController::class, 'index']);
    Route::post('/hotels/{hotel_id}/reviews', [ReviewController::class, 'store']);
    Route::put('/hotels/{hotel_id}/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/hotels/{hotel_id}/reviews/{id}', [ReviewController::class, 'destroy']);

});

use App\Http\Controllers\DashboardController;
Route::middleware('auth:sanctum')->get('/dashboard/stats', [DashboardController::class, 'stats']);

use App\Http\Controllers\ActivityLogController;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index']); // Admin
    Route::get('/activity-logs/my', [ActivityLogController::class, 'myActivity']); // User
});

use App\Http\Controllers\NotificationController;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});