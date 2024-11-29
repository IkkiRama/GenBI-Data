<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request) {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        $request->validate([
            "email" => "required|email|string",
            "password" => "required"
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Invalid email or password"
            ], 401, $headers);
        }

        // Cek apakah pengguna sudah memiliki token aktif
        if ($user->tokens()->exists()) {
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Anda sudah login. Logout terlebih dahulu sebelum login kembali."
            ], 403, $headers); // 403 Forbidden
        }

        $token = $user->createToken("API Token")->plainTextToken;

        return response()->json([
            "success" => true,
            "data" =>[
                "access token" => $token,
                "token_type" => "Bearer",
                "user" => $user
            ],
            "message" => "Login Sukses"
        ], 200, $headers);
    }

    public function logout(Request $request)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        // Mendapatkan pengguna yang sedang login melalui token
        $user = $request->user();

        if ($user) {
            // Revoke semua token pengguna ini
            $user->tokens()->delete();

            return response()->json([
                "success" => true,
                "data" => null,
                "message" => "Logout berhasil"
            ], 200, $headers);
        }

        return response()->json([
            "success" => false,
            "data" => null,
            "message" => "Tidak ada sesi login yang aktif"
        ], 401, $headers);
    }

}
