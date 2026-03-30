<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'foto' => $googleUser->getAvatar(),
                    'password' => bcrypt('google-login'),
                ]
            );

             // Login user ke session Laravel
            Auth::login($user);

            // Optional: simpan data tambahan di session jika mau
            Session::put('google_user', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->foto,
            ]);

            // Redirect ke frontend tanpa token di URL
            return redirect(env('FRONTEND_URL') . '/dashboard');

        } catch (\Exception $e) {
            return redirect(env('FRONTEND_URL') . '/login?error=google');
        }
    }

    // Logout user
    public function logout()
    {
        // Hapus semua session
        Session::flush();

        // Logout dari Laravel auth
        Auth::logout();

        // Redirect ke halaman login atau homepage
        return redirect(env('FRONTEND_URL') . '/login');
    }
}
