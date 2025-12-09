<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    
    // GET /api/user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}