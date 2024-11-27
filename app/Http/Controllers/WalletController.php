<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'balance' => 'required|numeric|min:0.01',
        ]);

        if ($user->wallet) {
            return response()->json(['error' => 'Wallet already exists'], 400);
        }

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => $validated['balance'],
        ]);

        return response()->json(['message' => 'Wallet successfully created', 'data:' => $wallet]);
    }
    public function balance()
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['error' => 'Wallet not found'], 404);
        }

        return response()->json(['balance' => $wallet->balance], 200);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = auth()->user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient funds'], 400);
        }

        // Decrease sender's balance
        $wallet->balance -= $request->amount;
        $wallet->save();

        // Increase recipient's balance
        $recipientWallet = Wallet::firstOrCreate(['user_id' => $request->recipient_id]);
        $recipientWallet->balance += $request->amount;
        $recipientWallet->save();

        return response()->json(['message' => 'Transfer successful'], 200);
    }

}
