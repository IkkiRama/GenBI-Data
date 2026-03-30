<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
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

            //CREATE TOKEN
            $token = $user->createToken('auth_token')->plainTextToken;

            //REDIRECT BAWA TOKEN
            return redirect(env('FRONTEND_URL') . "/auth-success?token=" . $token);

        } catch (\Exception $e) {
            return redirect(env('FRONTEND_URL') . '/login?error=google');
        }
    }

    // Logout user
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ]);
    }
}
