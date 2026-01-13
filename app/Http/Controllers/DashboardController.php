<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'hotels' => Hotel::count(),
            'bookings' => Booking::count(),
            'users' => User::count(),
            'reviews' => Review::count(),
            'total_wallet' => User::sum('wallet_balance'),
        ]);
    }

}
