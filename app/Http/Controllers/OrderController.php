<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function initiate(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Get the user's wallet
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $validated['amount']) {
            return response()->json(['error' => 'Insufficient funds'], 400);
        }

        // Deduct the amount from the user's wallet balance
        $wallet->balance -= $validated['amount'];
        $wallet->save();

        // Create the order
        $order = Order::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'status' => 'pending',  // Order status starts as pending
        ]);

        return response()->json(['message' => 'Order initiated successfully', 'order' => $order], 201);
    }

    public function status(Order $order)
    {
        // Ensure the authenticated user owns the order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['order' => $order], 200);
    }

}
