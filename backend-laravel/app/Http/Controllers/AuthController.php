<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'nullable|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'username' => $fields['username'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => 'KASIR',
            'isActive' => true
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return $this->sendResponse([
            'user' => $user,
            'token' => $token
        ], 'User registered successfully');
    }



    public function login(Request $request)
    {
        Log::info('Login Attempt', ['username' => $request->username]);

        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check username (Case insensitive search for Postgres)
        $user = User::whereRaw('LOWER(username) = ?', [strtolower($fields['username'])])->first();

        if (!$user) {
            Log::warning('Login Failed: User not found', ['username' => $fields['username']]);
            return $this->sendError('Bad creds', [], 401);
        }

        // Check password
        if (!Hash::check($fields['password'], $user->password)) {
            Log::warning('Login Failed: Password mismatch', ['username' => $fields['username']]);
            return $this->sendError('Bad creds', [], 401);
        }

        Log::info('Login Success', ['user_id' => $user->id]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return $this->sendResponse([
            'user' => $user,
            'token' => $token
        ], 'User logged in successfully');
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return $this->sendResponse([], 'Logged out successfully');
    }

    public function me(Request $request)
    {
        return $this->sendResponse($request->user(), 'User data retrieved');
    }
}
