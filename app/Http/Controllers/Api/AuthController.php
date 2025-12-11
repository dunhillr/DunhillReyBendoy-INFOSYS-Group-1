<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Check Credentials
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        // 3. Generate Token
        $user = User::where('email', $request->email)->firstOrFail();
        
        // Delete old tokens to keep it clean (optional)
        // $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Return JSON
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    // POST /api/register
    public function register(Request $request)
    {
        // 1. Validate Input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // 'confirmed' checks 'password_confirmation'
        ]);

        // 2. Create User
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Create Token (Auto-login)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Return Response
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token, // Return token so app can save it
            ]
        ], 201);
    }
    
    // GET /api/user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}