<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Library;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    /**
     * List all users (students / members) for Admin.
     */
    public function getUsers(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['user', 'member'])->withCount(['bookings', 'payments'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->paginate(20)->withQueryString();

        return response()->json([
            'success' => true,
            'total' => $users->total(),
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ], 200);
    }

    /**
     * List all registered libraries and their owners for Admin.
     */
    public function getLibraries(Request $request): JsonResponse
    {
        $query = Library::with(['owner:id,name,email,phone,status,created_at'])
            ->withCount([
                'seats as total_seats_count',
                'seats as available_seats_count' => function ($q) {
                    $q->where('status', 'available');
                },
                'seats as occupied_seats_count' => function ($q) {
                    $q->where('status', 'occupied');
                },
            ])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($oq) use ($search) {
                        $oq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $libraries = $query->paginate(20)->withQueryString();

        return response()->json([
            'success' => true,
            'total' => $libraries->total(),
            'data' => $libraries->items(),
            'pagination' => [
                'current_page' => $libraries->currentPage(),
                'last_page' => $libraries->lastPage(),
            ],
        ], 200);
    }

    /**
     * View seats and availability across all libraries for Admin.
     */
    public function getSeatsOverview(Request $request): JsonResponse
    {
        $libraryId = $request->input('library_id');

        $seatQuery = Seat::with(['library:id,name', 'zone:id,name']);

        if ($libraryId) {
            $seatQuery->where('library_id', $libraryId);
        }

        if ($status = $request->input('status')) {
            $seatQuery->where('status', $status);
        }

        $seats = $seatQuery->paginate(30)->withQueryString();

        $allSeats = Seat::query();
        if ($libraryId) {
            $allSeats->where('library_id', $libraryId);
        }

        $summary = [
            'total' => (clone $allSeats)->count(),
            'available' => (clone $allSeats)->where('status', 'available')->count(),
            'occupied' => (clone $allSeats)->where('status', 'occupied')->count(),
            'reserved' => (clone $allSeats)->where('status', 'reserved')->count(),
            'maintenance' => (clone $allSeats)->where('status', 'maintenance')->count(),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $seats->items(),
            'pagination' => [
                'current_page' => $seats->currentPage(),
                'last_page' => $seats->lastPage(),
            ],
        ], 200);
    }

    /**
     * View all payments for Admin (Library registration fees via QR/Razorpay & student bookings).
     */
    public function getPayments(Request $request): JsonResponse
    {
        $query = Payment::with(['user:id,name,email,phone', 'library:id,name', 'plan:id,name', 'booking.seat'])
            ->latest();

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20)->withQueryString();

        // Calculate metrics
        $metrics = [
            'total_revenue' => (float) Payment::where('status', 'completed')->sum('amount'),
            'library_registration_fees' => (float) Payment::where('type', 'library_registration')->where('status', 'completed')->sum('amount'),
            'seat_booking_fees' => (float) Payment::where('type', 'seat_booking')->where('status', 'completed')->sum('amount'),
            'upi_total' => (float) Payment::where('payment_method', 'upi')->where('status', 'completed')->sum('amount'),
            'razorpay_total' => (float) Payment::where('payment_method', 'online_razorpay')->where('status', 'completed')->sum('amount'),
            'cash_total' => (float) Payment::where('payment_method', 'cash')->where('status', 'completed')->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
            'data' => $payments->items(),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
        ], 200);
    }
}
