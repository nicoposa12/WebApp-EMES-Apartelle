<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Split name if possible
                $nameParts = explode(' ', $googleUser->getName(), 2);
                $firstName = $nameParts[0] ?? $googleUser->getName();
                $lastName = $nameParts[1] ?? '';

                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'role' => 'guest',
                    // Dummy data for required fields if not provided by Google
                    'phone' => 'N/A',
                    'address' => 'N/A',
                    'city' => 'N/A',
                    'zip_code' => 'N/A',
                    'birthdate' => now()->subYears(18)->format('Y-m-d'),
                    'gender' => 'other',
                    'profile_photo_path' => $googleUser->getAvatar(),
                ]);
            } else {
                // Update profile photo if it's from google and has changed
                if ($googleUser->getAvatar() && $user->profile_photo_path !== $googleUser->getAvatar()) {
                    $user->update(['profile_photo_path' => $googleUser->getAvatar()]);
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect to frontend with token
            $frontendUrl = config('app.url');
            return redirect()->away($frontendUrl . "/login?token=" . $token);

        } catch (\Exception $e) {
            return redirect()->away(config('app.url') . "/login?error=social_auth_failed");
        }
    }
}
