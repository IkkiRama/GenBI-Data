<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login user dan menghasilkan token autentikasi (Bearer Token).
     * - Validasi email dan password
     * - Cek apakah user terdaftar dan password sesuai
     * - Hapus token lama (hanya mengizinkan 1 sesi per user)
     * - Kembalikan token baru dan data user
     */
    public function login(Request $request)
    {
        // Validasi input yang diterima dari user
        $request->validate([
            "email" => "required|email|string",
            "password" => "required|string"
        ]);

        // Cari user berdasarkan email
        $user = User::where("email", $request->email)->first();

        // Jika user tidak ditemukan atau password salah → kembalikan error 401
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Email atau password tidak valid"
            ], 401);
        }

        // Hapus semua token lama user (single login per user)
        // Jika ingin multi device login, bagian ini bisa dihapus
        $user->tokens()->delete();

        // Buat token baru untuk autentikasi API
        $token = $user->createToken("API Token")->plainTextToken;

        // Kembalikan response sukses ke client
        return response()->json([
            "success" => true,
            "data" => [
                "token" => $token,         // Token yang digunakan pada header Authorization
                "token_type" => "Bearer",  // Jenis token
                "user" => $user            // Data user yang login
            ],
            "message" => "Login berhasil"
        ], 200);
    }

    /**
     * Logout user dan mencabut token aktif.
     * - Pastikan ada user yang login
     * - Hapus token dari database (revoke)
     */
    public function logout(Request $request)
    {
        // Ambil user berdasarkan token yang sedang aktif
        $user = $request->user();

        // Jika ada user yang aktif → hapus semua token (logout semua sesi)
        if ($user) {
            $user->tokens()->delete();

            return response()->json([
                "success" => true,
                "data" => null,
                "message" => "Logout berhasil"
            ], 200);
        }

        // Jika tidak ada token aktif → unauthorized
        return response()->json([
            "success" => false,
            "data" => null,
            "message" => "Tidak ada sesi login yang aktif"
        ], 401);
    }
}
