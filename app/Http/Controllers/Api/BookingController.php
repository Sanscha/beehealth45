<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Seat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Reserve/book a seat for a member.
     */
    public function store(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'seat_id' => 'required|exists:seats,id',
            'shift' => 'required|in:morning,evening,full_day',
            'booking_date' => 'required|date_format:Y-m-d',
        ]);

        $seat = Seat::with('zone')->findOrFail($fields['seat_id']);

        if ($seat->status === 'maintenance') {
            return response()->json([
                'success' => false,
                'message' => 'This seat is currently under maintenance and cannot be booked.',
            ], 422);
        }

        // Check for conflicting active bookings for this seat and date
        $conflict = Booking::where('seat_id', $seat->id)
            ->where('booking_date', $fields['booking_date'])
            ->where('status', 'active')
            ->where(function ($query) use ($fields) {
                if ($fields['shift'] === 'full_day') {
                    // Conflicts with any shift
                    $query->whereIn('shift', ['morning', 'evening', 'full_day']);
                } else {
                    $query->where('shift', $fields['shift'])
                        ->orWhere('shift', 'full_day');
                }
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'This seat is already booked for the selected date and shift.',
            ], 422);
        }

        $passCode = Booking::generatePassCode();

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'seat_id' => $seat->id,
            'pass_code' => $passCode,
            'shift' => $fields['shift'],
            'booking_date' => $fields['booking_date'],
            'status' => 'active',
        ]);

        // Mark seat status as reserved
        $seat->status = 'reserved';
        $seat->save();

        return response()->json([
            'success' => true,
            'message' => 'Seat booked successfully. Please present your pass code upon entry.',
            'booking' => $booking->load(['seat.zone', 'user:id,name,email,phone']),
        ], 201);
    }

    /**
     * Get bookings of the authenticated member.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = Booking::with(['seat.zone'])
            ->where('user_id', $request->user()->id)
            ->orderBy('booking_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ], 200);
    }

    /**
     * Cancel an active booking.
     */
    public function cancelBooking(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::findOrFail($id);

        if (! $user->isStaff() && $booking->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this booking.',
            ], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already cancelled.',
            ], 422);
        }

        $booking->status = 'cancelled';
        $booking->save();

        // Release the seat back to available if no other active booking exists for today
        $hasOtherActiveBooking = Booking::where('seat_id', $booking->seat_id)
            ->where('booking_date', $booking->booking_date)
            ->where('status', 'active')
            ->exists();

        if (! $hasOtherActiveBooking) {
            $seat = Seat::find($booking->seat_id);
            if ($seat && $seat->status !== 'maintenance') {
                $seat->status = 'available';
                $seat->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
        ], 200);
    }

    /**
     * Staff Action: Scan/Verify a member's pass code.
     */
    public function verifyPass(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'pass_code' => 'required|string',
        ]);

        $booking = Booking::with(['user:id,name,email,phone', 'seat.zone'])
            ->where('pass_code', trim($fields['pass_code']))
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Invalid pass code. No booking record found.',
            ], 404);
        }

        $isToday = $booking->booking_date === date('Y-m-d');

        return response()->json([
            'success' => true,
            'valid' => $booking->status === 'active' && $isToday,
            'is_today' => $isToday,
            'booking' => $booking,
        ], 200);
    }
}
