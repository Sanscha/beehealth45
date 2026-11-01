<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerSeatController extends Controller
{
    /**
     * Get Library Manager's dashboard overview and live seat statistics.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $library = $user->library;

        if (!$library) {
            return response()->json([
                'success' => false,
                'message' => 'No library linked to this manager account.',
            ], 404);
        }

        $seats = $library->seats;

        $stats = [
            'total_seats' => $seats->count(),
            'available_seats' => $seats->where('status', 'available')->count(),
            'occupied_seats' => $seats->where('status', 'occupied')->count(),
            'reserved_seats' => $seats->where('status', 'reserved')->count(),
            'maintenance_seats' => $seats->where('status', 'maintenance')->count(),
            'occupancy_percentage' => $seats->count() > 0
                ? round((($seats->whereIn('status', ['occupied', 'reserved'])->count()) / $seats->count()) * 100, 1)
                : 0,
        ];

        return response()->json([
            'success' => true,
            'library' => $library,
            'statistics' => $stats,
        ], 200);
    }

    /**
     * Enter/configure total number of seats and automatically generate them.
     */
    public function setupSeats(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'total_seats' => 'required|integer|min:1|max:1000',
            'prefix' => 'nullable|string|max:10',
            'replace_existing' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $library = $user->library;

        if (!$library) {
            return response()->json([
                'success' => false,
                'message' => 'No library linked to this manager account.',
            ], 404);
        }

        $totalSeats = (int) $fields['total_seats'];
        $prefix = !empty($fields['prefix']) ? trim($fields['prefix']) : 'S-';
        $replaceExisting = (bool) ($fields['replace_existing'] ?? false);

        if ($replaceExisting) {
            $library->seats()->delete();
        }

        $existingCount = $library->seats()->count();
        $start = $existingCount + 1;
        $end = $existingCount + $totalSeats;

        $createdSeats = [];
        for ($i = $start; $i <= $end; $i++) {
            $seatNumber = $prefix . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            // Avoid duplicate seat numbers
            $exists = $library->seats()->where('seat_number', $seatNumber)->exists();
            if (!$exists) {
                $seat = Seat::create([
                    'library_id' => $library->id,
                    'seat_number' => $seatNumber,
                    'status' => 'available',
                ]);
                $createdSeats[] = $seat;
            }
        }

        // Update library total seats count
        $library->total_seats = $library->seats()->count();
        $library->save();

        return response()->json([
            'success' => true,
            'message' => 'Configured ' . count($createdSeats) . " seats for {$library->name}.",
            'total_seats' => $library->total_seats,
            'created_count' => count($createdSeats),
            'seats' => $library->seats()->orderBy('seat_number')->get(),
        ], 200);
    }

    /**
     * Get list of all seats in the manager's library.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $library = $user->library;

        if (!$library) {
            return response()->json([
                'success' => false,
                'message' => 'No library linked to this manager account.',
            ], 404);
        }

        $query = $library->seats()->orderBy('seat_number');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $seats = $query->get();

        return response()->json([
            'success' => true,
            'library_name' => $library->name,
            'total' => $seats->count(),
            'data' => $seats,
        ], 200);
    }

    /**
     * Add an individual seat to the library.
     */
    public function store(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'seat_number' => 'required|string|max:20',
            'status' => 'nullable|in:available,occupied,reserved,maintenance',
        ]);

        $user = $request->user();
        $library = $user->library;

        if (!$library) {
            return response()->json([
                'success' => false,
                'message' => 'No library linked to this manager account.',
            ], 404);
        }

        // Check if seat already exists in this library
        if ($library->seats()->where('seat_number', $fields['seat_number'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Seat '{$fields['seat_number']}' already exists in your library.",
            ], 422);
        }

        $seat = Seat::create([
            'library_id' => $library->id,
            'seat_number' => $fields['seat_number'],
            'status' => $fields['status'] ?? 'available',
        ]);

        $library->total_seats = $library->seats()->count();
        $library->save();

        return response()->json([
            'success' => true,
            'message' => "Seat '{$seat->seat_number}' added successfully.",
            'seat' => $seat,
        ], 201);
    }

    /**
     * Update/Toggle seat availability status (e.g. available vs occupied vs maintenance).
     */
    public function updateStatus(Request $request, int|string $id): JsonResponse
    {
        $fields = $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        $user = $request->user();
        $library = $user->library;

        if (!$library) {
            return response()->json([
                'success' => false,
                'message' => 'No library linked to this manager account.',
            ], 404);
        }

        $seat = $library->seats()->findOrFail($id);
        $seat->status = $fields['status'];
        $seat->save();

        return response()->json([
            'success' => true,
            'message' => "Seat '{$seat->seat_number}' status changed to '{$seat->status}'.",
            'seat' => $seat,
        ], 200);
    }

    /**
     * Delete an individual seat.
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $library = $user->library;

        if (!$library) {
            return response()->json([
                'success' => false,
                'message' => 'No library linked to this manager account.',
            ], 404);
        }

        $seat = $library->seats()->findOrFail($id);
        $seat->delete();

        $library->total_seats = $library->seats()->count();
        $library->save();

        return response()->json([
            'success' => true,
            'message' => "Seat '{$seat->seat_number}' deleted successfully.",
        ], 200);
    }
}
