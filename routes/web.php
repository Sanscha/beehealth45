<?php

use App\Http\Controllers\Web\AdminController;
use Illuminate\Support\Facades\Route;

// Redirect root to Admin Dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Authentication
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Protected Staff & Admin Dashboard Routes
Route::middleware('staff')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Libraries & Owners Platform Management
    Route::get('/libraries', [AdminController::class, 'libraries'])->name('libraries');
    Route::post('/libraries/{id}/toggle-status', [AdminController::class, 'toggleLibraryStatus'])->name('libraries.toggle-status');
    Route::post('/libraries/{id}/verify-payment', [AdminController::class, 'verifyLibraryPayment'])->name('libraries.verify-payment');

    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');

    // Seat & Zone Management
    Route::get('/seats', [AdminController::class, 'seats'])->name('seats');
    Route::post('/zones', [AdminController::class, 'storeZone'])->name('zones.store');
    Route::post('/seats', [AdminController::class, 'storeSeat'])->name('seats.store');
    Route::post('/seats/batch', [AdminController::class, 'batchCreateSeats'])->name('seats.batch');
    Route::post('/seats/{id}/status', [AdminController::class, 'updateSeatStatus'])->name('seats.update-status');

    // Bookings & QR Passes
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::post('/bookings/{id}/checkin', [AdminController::class, 'checkInBooking'])->name('bookings.checkin');
    Route::post('/bookings/{id}/cancel', [AdminController::class, 'cancelBooking'])->name('bookings.cancel');

    // Payments & Billing Module
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::post('/payments', [AdminController::class, 'storePayment'])->name('payments.store');
    Route::post('/plans', [AdminController::class, 'storePlan'])->name('plans.store');
    Route::post('/plans/{id}/toggle', [AdminController::class, 'togglePlan'])->name('plans.toggle');
});
