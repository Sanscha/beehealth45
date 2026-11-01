<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Library;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Seat;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Show Admin Login Page.
     */
    public function loginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle Admin/Staff Web Login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])
            ->orWhere('phone', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withInput()->with('error', 'Invalid email or password.');
        }

        if (! $user->isStaff()) {
            return back()->withInput()->with('error', 'Access denied. You do not have staff or administrator privileges.');
        }

        if (! $user->isActive()) {
            return back()->withInput()->with('error', 'Your staff account has been deactivated. Contact an administrator.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, '.$user->name.'!');
    }

    /**
     * Log out admin/staff session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }

    /**
     * Dashboard Overview with Live Floor Map and Key Metrics.
     */
    public function dashboard(): View
    {
        $today = date('Y-m-d');

        $totalMembers = User::whereIn('role', ['user', 'member'])->count();
        $totalManagers = User::where('role', 'manager')->count();
        $totalLibraries = Library::count();
        $activeLibraries = Library::where('status', 'active')->count();
        $pendingLibraries = Library::where('payment_status', 'unpaid')->count();
        $libraryRegRevenue = Payment::where('type', 'library_registration')->where('status', 'completed')->sum('amount');

        $totalSeats = Seat::count();
        $occupiedSeats = Seat::where('status', 'occupied')->count();
        $reservedSeats = Seat::where('status', 'reserved')->count();
        $availableSeats = Seat::where('status', 'available')->count();
        $maintenanceSeats = Seat::where('status', 'maintenance')->count();

        $occupancyRate = $totalSeats > 0 ? round((($occupiedSeats + $reservedSeats) / $totalSeats) * 100, 1) : 0;

        $todayBookingsCount = Booking::where('booking_date', $today)->where('status', '!=', 'cancelled')->count();
        $totalRevenueToday = Payment::whereDate('created_at', $today)->where('status', 'completed')->sum('amount');
        $totalRevenueMonth = Payment::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('status', 'completed')->sum('amount');

        $zones = Zone::with(['seats' => function ($query) {
            $query->orderBy('seat_number');
        }])->get();

        $recentPayments = Payment::with(['user', 'library', 'plan', 'collector'])
            ->latest()
            ->limit(6)
            ->get();

        $recentBookings = Booking::with(['user', 'seat.zone'])
            ->latest()
            ->limit(6)
            ->get();

        $recentLibraries = Library::with('owner')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalMembers',
            'totalManagers',
            'totalLibraries',
            'activeLibraries',
            'pendingLibraries',
            'libraryRegRevenue',
            'totalSeats',
            'occupiedSeats',
            'reservedSeats',
            'availableSeats',
            'maintenanceSeats',
            'occupancyRate',
            'todayBookingsCount',
            'totalRevenueToday',
            'totalRevenueMonth',
            'zones',
            'recentPayments',
            'recentBookings',
            'recentLibraries'
        ));
    }

    /**
     * Libraries & Owners Management Module.
     */
    public function libraries(Request $request): View
    {
        $query = Library::with(['owner', 'seats'])
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

        $libraries = $query->paginate(15)->withQueryString();

        $metrics = [
            'total' => Library::count(),
            'active' => Library::where('status', 'active')->count(),
            'pending_payment' => Library::where('payment_status', 'unpaid')->count(),
            'reg_revenue' => Payment::where('type', 'library_registration')->where('status', 'completed')->sum('amount'),
        ];

        return view('admin.libraries', compact('libraries', 'metrics'));
    }

    /**
     * Toggle Library Status (Active / Suspended).
     */
    public function toggleLibraryStatus(int|string $id): RedirectResponse
    {
        $library = Library::findOrFail($id);
        $library->status = ($library->status === 'active') ? 'suspended' : 'active';
        $library->save();

        return back()->with('success', "Library '{$library->name}' status changed to {$library->status}.");
    }

    /**
     * Verify / Manually Mark Library Onboarding Payment as Paid.
     */
    public function verifyLibraryPayment(Request $request, int|string $id): RedirectResponse
    {
        $library = Library::with('owner')->findOrFail($id);

        if ($library->payment_status === 'paid') {
            return back()->with('error', "Library '{$library->name}' is already marked as paid.");
        }

        $library->payment_status = 'paid';
        $library->status = 'active';
        $library->save();

        // Record a payment entry for audit
        Payment::create([
            'user_id' => $library->user_id,
            'library_id' => $library->id,
            'amount' => $library->registration_fee,
            'type' => 'library_registration',
            'currency' => 'INR',
            'payment_method' => $request->input('payment_method', 'upi'),
            'transaction_id' => Payment::generateTransactionId('ADM-VERIFY'),
            'status' => 'completed',
            'collected_by' => Auth::id(),
            'notes' => 'Manually verified and activated by Admin ' . Auth::user()->name,
        ]);

        return back()->with('success', "Library '{$library->name}' registration fee verified and library activated.");
    }

    /**
     * User Management Module.
     */
    public function users(Request $request): View
    {
        $query = User::with(['library'])->withCount(['bookings', 'payments'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->paginate(15)->withQueryString();

        $counts = [
            'total' => User::count(),
            'members' => User::whereIn('role', ['user', 'member'])->count(),
            'managers' => User::where('role', 'manager')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users', compact('users', 'counts'));
    }

    /**
     * Create a new user (Member, Manager, or Staff) from Admin Panel.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'role' => 'required|in:admin,staff,member,manager',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'status' => 'active',
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', "User '{$validated['name']}' created successfully.");
    }

    /**
     * Toggle User Status (Active / Blocked).
     */
    public function toggleUserStatus(int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot block your own account.');
        }

        $user->status = ($user->status === 'active') ? 'blocked' : 'active';
        $user->save();

        return back()->with('success', "User '{$user->name}' status changed to {$user->status}.");
    }

    /**
     * Zone & Seat Management Module.
     */
    public function seats(Request $request): View
    {
        $zones = Zone::with(['seats' => function ($query) {
            $query->orderBy('seat_number');
        }])->get();

        $selectedZoneId = $request->input('zone_id');
        $seatQuery = Seat::with(['zone', 'activeBooking.user'])->orderBy('seat_number');

        if ($selectedZoneId) {
            $seatQuery->where('zone_id', $selectedZoneId);
        }

        $seats = $seatQuery->paginate(24)->withQueryString();

        return view('admin.seats', compact('zones', 'seats', 'selectedZoneId'));
    }

    /**
     * Create a new library floor/zone.
     */
    public function storeZone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:zones,code',
        ]);

        Zone::create($validated);

        return back()->with('success', "Zone '{$validated['name']}' created successfully.");
    }

    /**
     * Create a single seat.
     */
    public function storeSeat(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'seat_number' => 'required|string|max:20',
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        Seat::create($validated);

        return back()->with('success', "Seat '{$validated['seat_number']}' created successfully.");
    }

    /**
     * Batch generate seats in a zone (e.g. G-01 to G-20).
     */
    public function batchCreateSeats(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'prefix' => 'required|string|max:10',
            'start' => 'required|integer|min:1|max:999',
            'end' => 'required|integer|min:1|max:999|gte:start',
        ]);

        $createdCount = 0;
        $zone = Zone::findOrFail($validated['zone_id']);

        for ($i = $validated['start']; $i <= $validated['end']; $i++) {
            $seatNumber = trim($validated['prefix']).str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            // Avoid duplicate seat numbers in same zone
            $exists = Seat::where('zone_id', $zone->id)->where('seat_number', $seatNumber)->exists();
            if (! $exists) {
                Seat::create([
                    'zone_id' => $zone->id,
                    'seat_number' => $seatNumber,
                    'status' => 'available',
                ]);
                $createdCount++;
            }
        }

        return back()->with('success', "Generated {$createdCount} new seats in '{$zone->name}'.");
    }

    /**
     * Quick toggle seat status.
     */
    public function updateSeatStatus(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        $seat = Seat::findOrFail($id);
        $seat->status = $validated['status'];
        $seat->save();

        return back()->with('success', "Seat '{$seat->seat_number}' status updated to {$seat->status}.");
    }

    /**
     * Bookings & QR Pass Management Module.
     */
    public function bookings(Request $request): View
    {
        $query = Booking::with(['user', 'seat.zone'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('pass_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('seat', function ($sq) use ($search) {
                        $sq->where('seat_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($shift = $request->input('shift')) {
            $query->where('shift', $shift);
        }

        if ($date = $request->input('date')) {
            $query->where('booking_date', $date);
        }

        $bookings = $query->paginate(15)->withQueryString();

        return view('admin.bookings', compact('bookings'));
    }

    /**
     * Check-in / Complete a member's pass.
     */
    public function checkInBooking(int|string $id): RedirectResponse
    {
        $booking = Booking::with('seat')->findOrFail($id);
        $booking->status = 'completed';
        $booking->save();

        if ($booking->seat && $booking->seat->status === 'reserved') {
            $booking->seat->status = 'occupied';
            $booking->seat->save();
        }

        return back()->with('success', "Pass '{$booking->pass_code}' verified and member checked in.");
    }

    /**
     * Cancel a booking from Admin Panel.
     */
    public function cancelBooking(int|string $id): RedirectResponse
    {
        $booking = Booking::with('seat')->findOrFail($id);
        $booking->status = 'cancelled';
        $booking->save();

        if ($booking->seat && $booking->seat->status !== 'maintenance') {
            $booking->seat->status = 'available';
            $booking->seat->save();
        }

        return back()->with('success', "Booking '{$booking->pass_code}' cancelled and seat released.");
    }

    /**
     * Payments & Billing Module.
     */
    public function payments(Request $request): View
    {
        $today = date('Y-m-d');

        $metrics = [
            'totalRevenue' => Payment::where('status', 'completed')->sum('amount'),
            'todayRevenue' => Payment::whereDate('created_at', $today)->where('status', 'completed')->sum('amount'),
            'libraryRegRevenue' => Payment::where('type', 'library_registration')->where('status', 'completed')->sum('amount'),
            'seatBookingRevenue' => Payment::where('type', 'seat_booking')->where('status', 'completed')->sum('amount'),
            'cashTotal' => Payment::where('payment_method', 'cash')->where('status', 'completed')->sum('amount'),
            'upiTotal' => Payment::where('payment_method', 'upi')->where('status', 'completed')->sum('amount'),
            'onlineTotal' => Payment::whereIn('payment_method', ['online_razorpay', 'online_stripe'])->where('status', 'completed')->sum('amount'),
        ];

        $query = Payment::with(['user', 'library', 'plan', 'booking.seat.zone', 'collector'])->latest();

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('library', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(15)->withQueryString();
        $plans = Plan::all();
        $users = User::whereIn('role', ['member', 'user'])->where('status', 'active')->orderBy('name')->get();
        $activeBookings = Booking::with(['user', 'seat.zone'])->where('status', 'active')->get();

        return view('admin.payments', compact('metrics', 'payments', 'plans', 'users', 'activeBookings'));
    }

    /**
     * Record a manual counter payment (Cash or UPI) or online entry.
     */
    public function storePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'nullable|exists:plans,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,upi,online_razorpay,online_stripe,card',
            'transaction_id' => 'nullable|string|max:100',
            'status' => 'required|in:completed,pending,failed,refunded',
            'notes' => 'nullable|string|max:500',
        ]);

        $txnId = (! empty($validated['transaction_id'])) ? $validated['transaction_id'] : Payment::generateTransactionId(strtoupper($validated['payment_method']));

        Payment::create([
            'user_id' => $validated['user_id'],
            'plan_id' => $validated['plan_id'] ?? null,
            'booking_id' => $validated['booking_id'] ?? null,
            'collected_by' => Auth::id(),
            'amount' => $validated['amount'],
            'currency' => 'INR',
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $txnId,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Payment of ?{$validated['amount']} recorded successfully. Ref: {$txnId}");
    }

    /**
     * Create a new pricing / membership plan.
     */
    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'shift' => 'required|in:morning,evening,full_day,all',
            'description' => 'nullable|string|max:255',
        ]);

        Plan::create(array_merge($validated, ['is_active' => true]));

        return back()->with('success', "Plan '{$validated['name']}' created successfully.");
    }

    /**
     * Toggle a pricing plan's active status.
     */
    public function togglePlan(int|string $id): RedirectResponse
    {
        $plan = Plan::findOrFail($id);
        $plan->is_active = ! $plan->is_active;
        $plan->save();

        return back()->with('success', "Plan '{$plan->name}' status updated.");
    }
}
