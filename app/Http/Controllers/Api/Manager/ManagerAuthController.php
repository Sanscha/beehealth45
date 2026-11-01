<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Models\Library;
use App\Models\User;
use App\Services\OtpService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManagerAuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Register a Library Manager along with their Library details.
     */
    public function register(Request $request): JsonResponse
    {
        $fields = $request->validate([
            // Manager Personal Details
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',

            // Library Details
            'library_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'amenities' => 'nullable', // array or json string
            'image' => 'nullable', // string url or file upload
            'registration_fee' => 'nullable|numeric|min:0',
        ]);

        // 1. Create Manager User
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'phone' => $fields['phone'],
            'role' => 'manager',
            'status' => 'active',
            'email_verified_at' => null,
            'password' => Hash::make($fields['password']),
        ]);

        // 2. Handle Library Image (uploaded file or url string)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('libraries', 'public');
        } elseif (!empty($fields['image']) && is_string($fields['image'])) {
            $imagePath = $fields['image'];
        }

        // Parse amenities if passed as json string
        $amenities = $fields['amenities'] ?? ['WiFi', 'AC', 'Power Backup', 'RO Water'];
        if (is_string($amenities)) {
            $decoded = json_decode($amenities, true);
            if (is_array($decoded)) {
                $amenities = $decoded;
            }
        }

        $regFee = isset($fields['registration_fee']) ? (float) $fields['registration_fee'] : 999.00;

        // 3. Create Library Record
        $library = Library::create([
            'user_id' => $user->id,
            'name' => $fields['library_name'],
            'slug' => Library::createUniqueSlug($fields['library_name']),
            'image' => $imagePath,
            'address' => $fields['address'],
            'city' => $fields['city'] ?? null,
            'state' => $fields['state'] ?? null,
            'pincode' => $fields['pincode'] ?? null,
            'latitude' => $fields['latitude'] ?? null,
            'longitude' => $fields['longitude'] ?? null,
            'phone' => $fields['phone'],
            'email' => $fields['email'],
            'description' => $fields['description'] ?? null,
            'amenities' => $amenities,
            'total_seats' => 0,
            'registration_fee' => $regFee,
            'payment_status' => 'unpaid',
            'status' => 'pending_payment',
        ]);

        // 4. Send Email OTP for Manager verification
        $otpResult = $this->otpService->sendOtp($user->email, 'manager_register');

        // 5. Generate Payment Details for onboarding (UPI QR & Razorpay Order)
        $paymentRef = 'LIBREG-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        $upiDetails = $this->paymentService->generateUpiQr(
            $regFee,
            $paymentRef,
            "Reg fee for {$library->name}"
        );
        $razorpayOrder = $this->paymentService->createRazorpayOrder(
            $regFee,
            $paymentRef
        );

        return response()->json([
            'success' => true,
            'message' => 'Library registered successfully! Please verify your email OTP and complete the listing payment.',
            'manager' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'email_verified' => false,
            ],
            'library' => $library,
            'otp' => $otpResult,
            'payment' => [
                'amount' => $regFee,
                'currency' => 'INR',
                'ref_id' => $paymentRef,
                'upi' => $upiDetails,
                'razorpay' => $razorpayOrder,
            ],
        ], 201);
    }

    /**
     * Verify email OTP for Library Manager.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|string',
        ]);

        $isValid = $this->otpService->verifyOtp($fields['email'], $fields['otp'], 'manager_register');

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code.',
            ], 422);
        }

        $user = User::with('library')->where('email', $fields['email'])->firstOrFail();
        $user->email_verified_at = Carbon::now();
        $user->save();

        $token = $user->createToken('manager_library_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Manager email verified successfully!',
            'manager' => $user,
            'library' => $user->library,
            'token' => $token,
        ], 200);
    }

    /**
     * Library Manager Login.
     */
    public function login(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $fields['login'];
        $user = User::with('library')
            ->where(function ($q) use ($login) {
                $q->where('email', $login)->orWhere('phone', $login);
            })
            ->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Account is not registered as a Library Manager.',
            ], 403);
        }

        if (!$user->isEmailVerified()) {
            $otpResult = $this->otpService->sendOtp($user->email, 'manager_register');

            return response()->json([
                'success' => false,
                'requires_otp' => true,
                'message' => 'Email verification required. A new OTP has been sent to your email.',
                'email' => $user->email,
                'otp' => $otpResult,
            ], 403);
        }

        $token = $user->createToken('manager_library_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Manager login successful',
            'manager' => $user,
            'library' => $user->library,
            'token' => $token,
        ], 200);
    }
}
