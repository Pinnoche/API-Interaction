<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


/**
 * @OA\Info(
 *     title="Mini Global API Center",
 *     version="1.0.0",
 *     description="API documentation for MGAC"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Development Server"
 * )
 */

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/login",
     *     operationId="userLogin",
     *     tags={"Authentication"},
     *     summary="Log in a user and get a token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci...")
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
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     description="Enter the token provided after login"
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = Auth::user()->createToken('Personal Access Token')->accessToken;

        return response()->json(['message' => 'User Successfully Logged in', 'token' => $token], 200);
    }

    public function validateToken(Request $request)
    {
        if (Auth::check()) {
            return response()->json(['message' => 'Token is valid'], 200);
        }
        return response()->json(['error' => 'Invalid token'], 401);
    }

    public function refresh(Request $request)
    {
        $user = Auth::user();

        $newToken = $user->createToken('Personal Access Token')->accessToken;

        return response()->json(['token' => $newToken], 200);
    }
}
