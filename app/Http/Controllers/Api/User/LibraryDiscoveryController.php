<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Library;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryDiscoveryController extends Controller
{
    /**
     * Browse nearby libraries with real-time empty seat counts.
     */
    public function index(Request $request): JsonResponse
    {
        $lat = $request->filled('latitude') ? (float) $request->input('latitude') : null;
        $lng = $request->filled('longitude') ? (float) $request->input('longitude') : null;
        $radiusKm = (float) $request->input('radius_km', 50);

        $query = Library::query()
            ->where('status', 'active')
            ->withCount([
                'seats as total_seats_count',
                'seats as available_seats_count' => function ($q) {
                    $q->where('status', 'available');
                },
                'seats as occupied_seats_count' => function ($q) {
                    $q->where('status', 'occupied');
                },
            ]);

        // Search by keyword
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by city if specified
        if ($city = $request->input('city')) {
            $query->where('city', 'like', "%{$city}%");
        }

        // Apply distance filter if coordinates provided
        if ($lat !== null && $lng !== null) {
            $query->nearby($lat, $lng, $radiusKm);
        } else {
            $query->latest();
        }

        $libraries = $query->paginate(15)->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Libraries retrieved successfully',
            'data' => $libraries->items(),
            'pagination' => [
                'current_page' => $libraries->currentPage(),
                'last_page' => $libraries->lastPage(),
                'total' => $libraries->total(),
            ],
        ], 200);
    }

    /**
     * View specific library profile with all available/empty seats.
     */
    public function show(int|string $id): JsonResponse
    {
        $library = Library::where('status', 'active')
            ->withCount([
                'seats as total_seats_count',
                'seats as available_seats_count' => function ($q) {
                    $q->where('status', 'available');
                },
                'seats as occupied_seats_count' => function ($q) {
                    $q->where('status', 'occupied');
                },
            ])
            ->findOrFail($id);

        // Fetch available seats specifically
        $availableSeats = $library->seats()
            ->where('status', 'available')
            ->select('id', 'library_id', 'seat_number', 'status')
            ->orderBy('seat_number')
            ->get();

        // Also fetch all seats so user can see seat layout
        $allSeats = $library->seats()
            ->select('id', 'library_id', 'seat_number', 'status')
            ->orderBy('seat_number')
            ->get();

        return response()->json([
            'success' => true,
            'library' => $library,
            'summary' => [
                'total_seats' => $library->total_seats_count,
                'available_seats_count' => $library->available_seats_count,
                'occupied_seats_count' => $library->occupied_seats_count,
            ],
            'available_seats' => $availableSeats,
            'all_seats' => $allSeats,
        ], 200);
    }
}
