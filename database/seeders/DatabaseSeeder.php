<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Seat;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Staff Account
        $staff = User::create([
            'name' => 'Library Staff',
            'email' => 'staff@library.com',
            'phone' => '9876543210',
            'role' => 'staff',
            'status' => 'active',
            'password' => Hash::make('staff123'),
        ]);

        // 2. Create Admin Account
        $admin = User::create([
            'name' => 'Library Admin',
            'email' => 'admin@library.com',
            'phone' => '9876543211',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('admin123'),
        ]);

        // 3. Create Sample Member Accounts
        $member = User::create([
            'name' => 'John Doe',
            'email' => 'member@library.com',
            'phone' => '9123456780',
            'role' => 'member',
            'status' => 'active',
            'password' => Hash::make('member123'),
        ]);

        $member2 = User::create([
            'name' => 'Priya Sharma',
            'email' => 'priya@example.com',
            'phone' => '9822334455',
            'role' => 'member',
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        $member3 = User::create([
            'name' => 'Rahul Verma',
            'email' => 'rahul@example.com',
            'phone' => '9811223344',
            'role' => 'member',
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        // 4. Create Library Zones / Floors
        $zoneA = Zone::create(['name' => 'Ground Floor - Silent Zone', 'code' => 'GF-SZ']);
        $zoneB = Zone::create(['name' => 'First Floor - AC Hall', 'code' => 'FF-AC']);
        $zoneC = Zone::create(['name' => 'Second Floor - Discussion Hall', 'code' => 'SF-DH']);

        // 5. Create Seats for Each Zone
        $zones = [
            ['zone' => $zoneA, 'prefix' => 'G-'],
            ['zone' => $zoneB, 'prefix' => 'F-'],
            ['zone' => $zoneC, 'prefix' => 'D-'],
        ];

        $reservedSeat = null;
        $occupiedSeat = null;

        foreach ($zones as $zoneData) {
            $z = $zoneData['zone'];
            $prefix = $zoneData['prefix'];

            for ($i = 1; $i <= 10; $i++) {
                $status = 'available';
                if ($i % 4 === 0) {
                    $status = 'occupied';
                } elseif ($i === 3 && $prefix === 'G-') {
                    $status = 'reserved';
                }

                $seat = Seat::create([
                    'zone_id' => $z->id,
                    'seat_number' => $prefix.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'status' => $status,
                ]);

                if ($seat->seat_number === 'G-03') {
                    $reservedSeat = $seat;
                }
                if ($seat->seat_number === 'G-04') {
                    $occupiedSeat = $seat;
                }
            }
        }

        // 6. Pricing Plans
        $planMorning = Plan::create([
            'name' => 'Morning Shift Pass (8 AM - 2 PM)',
            'price' => 80.00,
            'duration_days' => 1,
            'shift' => 'morning',
            'description' => 'Single day entry for the silent study morning slot.',
            'is_active' => true,
        ]);

        $planEvening = Plan::create([
            'name' => 'Evening Shift Pass (2 PM - 8 PM)',
            'price' => 80.00,
            'duration_days' => 1,
            'shift' => 'evening',
            'description' => 'Single day entry for the evening slot.',
            'is_active' => true,
        ]);

        $planFullDay = Plan::create([
            'name' => 'Full Day Study Pass (8 AM - 8 PM)',
            'price' => 150.00,
            'duration_days' => 1,
            'shift' => 'full_day',
            'description' => 'Full access with high speed WiFi and power socket.',
            'is_active' => true,
        ]);

        $planMonthly = Plan::create([
            'name' => 'Monthly AC Study Pass (30 Days)',
            'price' => 2200.00,
            'duration_days' => 30,
            'shift' => 'all',
            'description' => 'Guaranteed AC hall seat reservation for 30 consecutive days.',
            'is_active' => true,
        ]);

        // 7. Create Active Sample Bookings
        $booking1 = null;
        if ($reservedSeat) {
            $booking1 = Booking::create([
                'user_id' => $member->id,
                'seat_id' => $reservedSeat->id,
                'pass_code' => 'LIB-PASS-001',
                'shift' => 'morning',
                'booking_date' => date('Y-m-d'),
                'status' => 'active',
            ]);
        }

        if ($occupiedSeat) {
            Booking::create([
                'user_id' => $member2->id,
                'seat_id' => $occupiedSeat->id,
                'pass_code' => 'LIB-PASS-002',
                'shift' => 'full_day',
                'booking_date' => date('Y-m-d'),
                'status' => 'active',
            ]);
        }

        // 8. Sample Payment Transactions
        if ($booking1) {
            Payment::create([
                'user_id' => $member->id,
                'booking_id' => $booking1->id,
                'plan_id' => $planMorning->id,
                'collected_by' => $staff->id,
                'amount' => 80.00,
                'currency' => 'INR',
                'payment_method' => 'cash',
                'transaction_id' => 'TXN-CASH-1001',
                'status' => 'completed',
                'notes' => 'Counter cash payment paid to reception.',
            ]);
        }

        Payment::create([
            'user_id' => $member2->id,
            'booking_id' => null,
            'plan_id' => $planFullDay->id,
            'collected_by' => $staff->id,
            'amount' => 150.00,
            'currency' => 'INR',
            'payment_method' => 'upi',
            'transaction_id' => 'UPI-9822334455@okaxis-01',
            'status' => 'completed',
            'notes' => 'Direct QR scan UPI payment.',
        ]);

        Payment::create([
            'user_id' => $member3->id,
            'booking_id' => null,
            'plan_id' => $planMonthly->id,
            'collected_by' => null,
            'amount' => 2200.00,
            'currency' => 'INR',
            'payment_method' => 'online_razorpay',
            'transaction_id' => 'pay_rzp_'.substr(md5('rzp3'), 0, 14),
            'status' => 'completed',
            'notes' => 'Online payment via Razorpay checkout.',
        ]);

        // 9. Create Library Manager (Owner) and Sample Libraries
        $manager = User::create([
            'name' => 'Vikram Malhotra',
            'email' => 'manager@library.com',
            'phone' => '9822001122',
            'role' => 'manager',
            'status' => 'active',
            'password' => Hash::make('manager123'),
        ]);

        $manager2 = User::create([
            'name' => 'Sunil Deshmukh',
            'email' => 'sunil@studyhaven.in',
            'phone' => '9822003344',
            'role' => 'manager',
            'status' => 'active',
            'password' => Hash::make('manager123'),
        ]);

        \App\Models\Library::create([
            'user_id' => $manager->id,
            'name' => 'Apex Central Study Hub',
            'slug' => 'apex-central-study-hub',
            'address' => '402 Prestige Point, Shukrawar Peth',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411002',
            'phone' => '9822001122',
            'email' => 'contact@apexcentral.in',
            'description' => 'Premium air-conditioned study hall with high-speed WiFi and individual ergonomic seating.',
            'amenities' => ['High Speed WiFi', 'Air Conditioned', 'Power Outlets', 'RO Water Dispenser', 'CCTV Security'],
            'total_seats' => 60,
            'registration_fee' => 499.00,
            'payment_status' => 'paid',
            'status' => 'active',
        ]);

        \App\Models\Library::create([
            'user_id' => $manager2->id,
            'name' => 'Study Haven Reading Room',
            'slug' => 'study-haven-reading-room',
            'address' => '12 Near Modern College, Shivajinagar',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411005',
            'phone' => '9822003344',
            'email' => 'info@studyhaven.in',
            'description' => 'Dedicated silent reading room designed for UPSC and competitive exam aspirants.',
            'amenities' => ['Silent Zones', 'Newspaper Desk', 'Lockers', 'Power Sockets'],
            'total_seats' => 40,
            'registration_fee' => 499.00,
            'payment_status' => 'unpaid',
            'status' => 'pending_payment',
        ]);

    }
}
