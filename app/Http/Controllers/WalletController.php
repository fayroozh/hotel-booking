<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class WalletController extends Controller
{
    // عرض رصيد المحفظة
    public function balance()
    {
        return response()->json([
            'balance' => Auth::user()->wallet_balance
        ]);
    }

    // عرض سجل المعاملات
    public function transactions()
    {
        $transactions = Auth::user()->transactions()->orderBy('created_at','desc')->get();
        return response()->json($transactions);
    }

    // شحن المحفظة (Admin / Manager فقط)
    public function credit(Request $request, $userId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        // فقط Admin أو Manager
        if (!in_array(Auth::user()->role, ['admin','manager'])) {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $user = User::findOrFail($userId);

        $user->wallet_balance += $request->amount;
        $user->save();

        $user->transactions()->create([
            'type' => 'credit',
            'amount' => $request->amount,
            'method' => 'manual',
            'description' => 'شحن محفظة من قبل '.Auth::user()->name,
        ]);

        return response()->json(['balance' => $user->wallet_balance]);
    }

    // خصم من المحفظة عند الحجز
    public function debitForBooking($userId, $amount)
    {
        $user = User::findOrFail($userId);

        if ($user->wallet_balance < $amount) {
            return false;
        }

        $user->wallet_balance -= $amount;
        $user->save();

        $user->transactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'method' => 'booking',
            'description' => 'خصم حجز',
        ]);

        return true;
    }
}
