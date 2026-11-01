<?php

use App\Http\Controllers\Api\Admin\AdminApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\Manager\ManagerAuthController;
use App\Http\Controllers\Api\Manager\ManagerPaymentController;
use App\Http\Controllers\Api\Manager\ManagerSeatController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\User\LibraryDiscoveryController;
use App\Http\Controllers\Api\User\UserAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes - Legacy / General Auth & Live Grid
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/seats/live-grid', [SeatController::class, 'getSeatGrid']);

/*
|--------------------------------------------------------------------------
| Student / User Endpoints (Auth, Email OTP, Google Login, Nearby Libraries)
|--------------------------------------------------------------------------
*/
Route::prefix('user')->group(function () {
    // Auth & OTP
    Route::post('/auth/register', [UserAuthController::class, 'register']);
    Route::post('/auth/verify-otp', [UserAuthController::class, 'verifyOtp']);
    Route::post('/auth/resend-otp', [UserAuthController::class, 'resendOtp']);
    Route::post('/auth/login', [UserAuthController::class, 'login']);
    Route::post('/auth/google', [UserAuthController::class, 'googleLogin']);

    // Library Discovery & Live Seat Availability
    Route::get('/libraries', [LibraryDiscoveryController::class, 'index']);
    Route::get('/libraries/{id}', [LibraryDiscoveryController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Library Manager Endpoints (Register, OTP, QR/Razorpay Payment, Seat Management)
|--------------------------------------------------------------------------
*/
Route::prefix('manager')->group(function () {
    // Manager Registration & Auth
    Route::post('/auth/register', [ManagerAuthController::class, 'register']);
    Route::post('/auth/verify-otp', [ManagerAuthController::class, 'verifyOtp']);
    Route::post('/auth/login', [ManagerAuthController::class, 'login']);

    // Manager Protected Controls
    Route::middleware(['auth:sanctum', 'manager'])->group(function () {
        // Registration Payment & QR Code details
        Route::get('/payment/details', [ManagerPaymentController::class, 'getPaymentDetails']);
        Route::post('/payment/verify', [ManagerPaymentController::class, 'verifyPayment']);

        // Dashboard & Seat Management
        Route::get('/dashboard', [ManagerSeatController::class, 'dashboard']);
        Route::post('/seats/setup', [ManagerSeatController::class, 'setupSeats']);
        Route::get('/seats', [ManagerSeatController::class, 'index']);
        Route::post('/seats', [ManagerSeatController::class, 'store']);
        Route::put('/seats/{id}/status', [ManagerSeatController::class, 'updateStatus']);
        Route::delete('/seats/{id}', [ManagerSeatController::class, 'destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| Super Admin API Endpoints (Users, Libraries & Owners, Seats, Payments)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'staff'])->group(function () {
    Route::get('/users', [AdminApiController::class, 'getUsers']);
    Route::get('/libraries', [AdminApiController::class, 'getLibraries']);
    Route::get('/seats', [AdminApiController::class, 'getSeatsOverview']);
    Route::get('/payments', [AdminApiController::class, 'getPayments']);
});

/*
|--------------------------------------------------------------------------
| Authenticated User Actions (Sanctum Protected)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Member Booking Actions
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancelBooking']);

    // Staff Reception Controls
    Route::middleware('staff')->group(function () {
        Route::put('/seats/{id}/status', [SeatController::class, 'updateSeatStatus']);
        Route::post('/bookings/verify-pass', [BookingController::class, 'verifyPass']);
    });
});
