<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

    /**
     * @OA\Post(
     *     path="/order/initiate",
     *     operationId="initiateOrder",
     *     tags={"Order"},
     *     summary="Initiate a new order",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", example=100.50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order initiated successfully"),
     *             @OA\Property(property="order", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="amount", type="number", example=100.50),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
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

    /**
     * @OA\Get(
     *     path="/order/status/{orderId}",
     *     operationId="getOrderStatus",
     *     tags={"Order"},
     *     summary="Retrieve the status of a specific order",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID of the order"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="order", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="amount", type="number", example=100.50),
     *                 @OA\Property(property="status", type="string", example="completed")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Order not found")
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
