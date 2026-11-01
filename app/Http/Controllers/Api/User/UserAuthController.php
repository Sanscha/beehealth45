<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Register a new student/user and send email OTP.
     */
    public function register(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'phone' => $fields['phone'],
            'role' => 'user',
            'status' => 'active',
            'email_verified_at' => null,
            'password' => Hash::make($fields['password']),
        ]);

        $otpResult = $this->otpService->sendOtp($user->email, 'user_register');

        return response()->json([
            'success' => true,
            'message' => 'Registration initiated. Please verify the 6-digit OTP sent to your email.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'email_verified' => false,
            ],
            'otp' => $otpResult,
        ], 201);
    }

    /**
     * Verify email OTP after user registration.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|string',
        ]);

        $isValid = $this->otpService->verifyOtp($fields['email'], $fields['otp'], 'user_register');

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code. Please request a new one.',
            ], 422);
        }

        $user = User::where('email', $fields['email'])->firstOrFail();
        $user->email_verified_at = Carbon::now();
        $user->save();

        $token = $user->createToken('user_library_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully! Welcome to SeatSync.',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Resend verification OTP to student.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ]);

        $otpResult = $this->otpService->sendOtp($fields['email'], 'user_register');

        return response()->json([
            'success' => true,
            'message' => 'A new verification code has been dispatched to your email.',
            'otp' => $otpResult,
        ], 200);
    }

    /**
     * Standard User Login via Email/Phone and Password.
     */
    public function login(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'login' => 'required|string', // Email or Phone
            'password' => 'required|string',
        ]);

        $login = $fields['login'];
        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is suspended. Please contact library administration.',
            ], 403);
        }

        // Check if email has been verified via OTP
        if (!$user->isEmailVerified()) {
            $otpResult = $this->otpService->sendOtp($user->email, 'user_register');

            return response()->json([
                'success' => false,
                'requires_otp' => true,
                'message' => 'Your email is not verified yet. A verification OTP has been sent to your email.',
                'email' => $user->email,
                'otp' => $otpResult,
            ], 403);
        }

        $token = $user->createToken('user_library_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Google Sign-In Endpoint.
     * Accepts Google OAuth token or Google profile payload.
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'name' => 'required|string',
            'google_id' => 'required|string',
            'avatar' => 'nullable|string',
        ]);

        $user = User::where('google_id', $fields['google_id'])
            ->orWhere('email', $fields['email'])
            ->first();

        if ($user) {
            // Update Google ID & avatar if not already linked
            $user->google_id = $fields['google_id'];
            if (!empty($fields['avatar'])) {
                $user->avatar = $fields['avatar'];
            }
            if ($user->email_verified_at === null) {
                $user->email_verified_at = Carbon::now();
            }
            $user->save();
        } else {
            // Auto-create user via Google
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'google_id' => $fields['google_id'],
                'avatar' => $fields['avatar'] ?? null,
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make(Str::random(24)),
            ]);
        }

        $token = $user->createToken('user_google_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Google authentication successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }
}
