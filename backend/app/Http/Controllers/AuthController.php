<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Login
    public function login (Request $request) {

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            return response()->json([
            'token' => $user -> createToken(time()) -> plainTextToken,
            'user' => $user
           ], 200);
        }

        return response()->json([
            'error' => 'Credentials do not match!'
        ],401);
    }

    // Register
    public function register (Request $request) {
        $exist = User::where("email", $request->email)->first();
        if ($exist) return response()->json([
            'error' => 'Email already exists. Use another one.'
        ], 401);

        $user = User::create([
            'name' => $request -> name,
            'email' => $request -> email,
            'password' => $request -> password,
        ]);

        return response()->json([
            'success' => 'Registered successfully.'
        ], 200);
    }
}
