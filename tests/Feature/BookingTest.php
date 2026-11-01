<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Seat;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_book_available_seat(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $token = $member->createToken('member_token')->plainTextToken;

        $zone = Zone::create(['name' => 'Silent Zone', 'code' => 'SZ-01']);
        $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S-05', 'status' => 'available']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/bookings', [
                'seat_id' => $seat->id,
                'shift' => 'morning',
                'booking_date' => date('Y-m-d'),
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'booking' => [
                    'seat_id' => $seat->id,
                    'user_id' => $member->id,
                    'shift' => 'morning',
                    'status' => 'active',
                ],
            ])
            ->assertJsonStructure([
                'booking' => ['pass_code'],
            ]);

        $this->assertDatabaseHas('bookings', [
            'seat_id' => $seat->id,
            'user_id' => $member->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('seats', [
            'id' => $seat->id,
            'status' => 'reserved',
        ]);
    }

    public function test_cannot_double_book_seat_for_same_shift(): void
    {
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $token2 = $member2->createToken('token2')->plainTextToken;

        $zone = Zone::create(['name' => 'Silent Zone', 'code' => 'SZ-01']);
        $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S-06', 'status' => 'available']);

        Booking::create([
            'user_id' => $member1->id,
            'seat_id' => $seat->id,
            'pass_code' => 'LIB-TEST-1',
            'shift' => 'morning',
            'booking_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token2)
            ->postJson('/api/bookings', [
                'seat_id' => $seat->id,
                'shift' => 'morning',
                'booking_date' => date('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'This seat is already booked for the selected date and shift.',
            ]);
    }

    public function test_member_can_view_my_bookings(): void
    {
        $member = User::factory()->create();
        $token = $member->createToken('token')->plainTextToken;

        $zone = Zone::create(['name' => 'Silent Zone', 'code' => 'SZ-01']);
        $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S-07', 'status' => 'available']);

        Booking::create([
            'user_id' => $member->id,
            'seat_id' => $seat->id,
            'pass_code' => 'LIB-TEST-2',
            'shift' => 'evening',
            'booking_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/my-bookings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_member_can_cancel_own_booking(): void
    {
        $member = User::factory()->create();
        $token = $member->createToken('token')->plainTextToken;

        $zone = Zone::create(['name' => 'Silent Zone', 'code' => 'SZ-01']);
        $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S-08', 'status' => 'reserved']);

        $booking = Booking::create([
            'user_id' => $member->id,
            'seat_id' => $seat->id,
            'pass_code' => 'LIB-CANCEL-1',
            'shift' => 'evening',
            'booking_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Booking cancelled successfully.',
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('seats', [
            'id' => $seat->id,
            'status' => 'available',
        ]);
    }

    public function test_staff_can_verify_pass_code(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $token = $staff->createToken('staff_token')->plainTextToken;

        $member = User::factory()->create(['name' => 'Verified Member']);
        $zone = Zone::create(['name' => 'Silent Zone', 'code' => 'SZ-01']);
        $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S-09', 'status' => 'reserved']);

        Booking::create([
            'user_id' => $member->id,
            'seat_id' => $seat->id,
            'pass_code' => 'LIB-VALID-PASS',
            'shift' => 'morning',
            'booking_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/bookings/verify-pass', [
                'pass_code' => 'LIB-VALID-PASS',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid' => true,
                'is_today' => true,
                'booking' => [
                    'pass_code' => 'LIB-VALID-PASS',
                    'user' => [
                        'name' => 'Verified Member',
                    ],
                ],
            ]);
    }
}
