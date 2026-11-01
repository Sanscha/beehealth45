<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    /**
     * Public/Member View: Fetch live status of all seats grouped by zone.
     */
    public function getSeatGrid(): JsonResponse
    {
        $seats = Seat::with(['zone:id,name,code'])
            ->select('id', 'zone_id', 'seat_number', 'status')
            ->get();

        $summary = [
            'total' => $seats->count(),
            'available' => $seats->where('status', 'available')->count(),
            'occupied' => $seats->where('status', 'occupied')->count(),
            'reserved' => $seats->where('status', 'reserved')->count(),
            'maintenance' => $seats->where('status', 'maintenance')->count(),
        ];

        $grouped = $seats->groupBy(function ($seat) {
            return $seat->zone ? $seat->zone->name : 'Unassigned';
        });

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $grouped,
        ], 200);
    }

    /**
     * Staff Action: Update seat availability state instantly.
     */
    public function updateSeatStatus(Request $request, int|string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        $seat = Seat::with('zone:id,name,code')->findOrFail($id);
        $seat->status = $request->input('status');
        $seat->save();

        return response()->json([
            'success' => true,
            'message' => 'Seat status updated successfully',
            'seat' => $seat,
        ], 200);
    }
}
