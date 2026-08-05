<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:20'],
            'birthdate' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'birthdate' => $request->birthdate,
            'gender' => $request->gender,
            'role' => 'guest',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_fingerprint' => ['nullable', 'string', 'max:128'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        // Bypass Multi-Factor Authentication for Administrator and Staff accounts
        if (in_array($user->role, ['admin', 'staff'])) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        }

        // Check if this device is already trusted (skip MFA for recognized devices)
        $fingerprint = $request->input('device_fingerprint');
        if ($fingerprint) {
            $trustedDevice = TrustedDevice::where('user_id', $user->id)
                ->where('device_fingerprint', $fingerprint)
                ->active()
                ->first();

            if ($trustedDevice) {
                // Device is recognized — update last used timestamp and skip MFA
                $trustedDevice->update([
                    'last_used_at' => Carbon::now(),
                    'ip_address' => $request->ip(),
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user,
                ]);
            }
        }

        // Device not recognized — generate a secure 6-digit numeric OTP code
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Save OTP and expiration time on user model
        $user->otp_code = $otp;
        $user->otp_expires_at = Carbon::now()->addSeconds(60);
        $user->save();

        try {
            // Dispatch Email Notification to user
            $user->notify(new \App\Notifications\SendOTPNotification($otp));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Email Send Failed: ' . $e->getMessage());
            // Proactively report in response if mailer is not available (useful for developer testing)
            return response()->json([
                'message' => 'Credentials verified, but failed to send verification email. Please check server mail configurations.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'mfa_required' => true,
            'email' => $request->email,
            'message' => 'A verification code has been sent to your email address.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'device_fingerprint' => ['nullable', 'string', 'max:128'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->otp_code || $user->otp_code !== $request->otp) {
            return response()->json([
                'message' => 'Invalid verification code.'
            ], 422);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'message' => 'Verification code has expired.'
            ], 422);
        }

        // Clear OTP columns
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // Save this device as trusted for 90 days so future logins skip MFA
        $fingerprint = $request->input('device_fingerprint');
        if ($fingerprint) {
            TrustedDevice::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_fingerprint' => $fingerprint,
                ],
                [
                    'device_name' => $this->parseDeviceName($request->userAgent()),
                    'ip_address' => $request->ip(),
                    'last_used_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addDays(90),
                ]
            );
        }

        // Create Sanctum access token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'], // 2MB Max
        ]);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
            $user->save();
        }

        return response()->json([
            'message' => 'Profile photo updated successfully',
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:20'],
            'birthdate' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'birthdate' => $request->birthdate,
            'gender' => $request->gender,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'The provided password does not match your current password.'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully.'
        ]);
    }

    /**
     * Parse a human-readable device name from the User-Agent string.
     * e.g. "Chrome on Windows", "Safari on macOS", "Firefox on Linux"
     */
    private function parseDeviceName(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown Device';
        }

        // Detect browser
        $browser = 'Unknown Browser';
        if (str_contains($userAgent, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera')) {
            $browser = 'Opera';
        } elseif (str_contains($userAgent, 'Chrome/') && !str_contains($userAgent, 'Edg/')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome/')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Firefox/')) {
            $browser = 'Firefox';
        }

        // Detect OS
        $os = 'Unknown OS';
        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux') && !str_contains($userAgent, 'Android')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        }

        return "{$browser} on {$os}";
    }
}
