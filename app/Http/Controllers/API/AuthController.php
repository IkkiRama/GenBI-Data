<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login & get Bearer Token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="example@gmail.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login Successful"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="Already logged in")
     * )
     */
    public function login(Request $request)
    {
        $headers = $this->getCorsHeaders();

        $request->validate([
            "email" => "required|email|string",
            "password" => "required|string"
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Email atau password salah"
            ], 401, $headers);
        }

        // Cek apakah sudah memiliki token aktif
        if ($user->tokens()->exists()) {
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Anda sudah login. Silahkan logout terlebih dahulu."
            ], 403, $headers);
        }

        $token = $user->createToken("API Token")->plainTextToken;

        return response()->json([
            "success" => true,
            "data" => [
                "token" => $token,
                "token_type" => "Bearer",
                "user" => $user
            ],
            "message" => "Login berhasil"
        ], 200, $headers);
    }


    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user & revoke token",
     *     security={{"bearerAuth":{}}},
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="No active session")
     * )
     */
    public function logout(Request $request)
    {
        $headers = $this->getCorsHeaders();

        $user = $request->user();

        if ($user) {
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


    /**
     * Helper untuk CORS Headers
     */
    private function getCorsHeaders()
    {
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $requestOrigin  = $_SERVER['HTTP_ORIGIN'] ?? '';

        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => in_array($requestOrigin, $allowedDomains) ? $requestOrigin : 'null',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }
}
