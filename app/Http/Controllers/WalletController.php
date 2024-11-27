<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class WalletController extends Controller
{

    /**
     * @OA\Get(
     *     path="/wallet/balance",
     *     operationId="getWalletBalance",
     *     tags={"Wallet"},
     *     summary="Fetch wallet balance for the authenticated user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="balance", type="number", example=100.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    /**
 * @OA\Get(
 *     path="/wallet/balance",
 *     operationId="getWalletBalance",
 *     tags={"Wallet"},
 *     summary="Fetch wallet balance for the authenticated user",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             @OA\Property(property="balance", type="number", example=250.75)
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Unauthorized")
 *         )
 *     )
 * )
 */

 /**
 * @OA\Post(
 *     path="/wallet/transfer",
 *     operationId="transferFunds",
 *     tags={"Wallet"},
 *     summary="Transfer funds to another user",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="recipient_id", type="integer", example=2),
 *             @OA\Property(property="amount", type="number", example=50.00)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful transfer",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transfer successful")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Insufficient funds",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Insufficient funds")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Unauthorized")
 *         )
 *     )
 * )
 */

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
