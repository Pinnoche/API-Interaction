<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'token' => $token,
            'data' => $user
        ], 201);
    }

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
    public function logout()
    {
        Auth::user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
