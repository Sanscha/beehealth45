<?php

namespace Tests\Feature;

use App\Models\Seat;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_seat_grid_returns_grouped_zones_and_summary(): void
    {
        $zone = Zone::create(['name' => 'Silent Zone', 'code' => 'SZ-01']);
        Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S-01', 'status' => 'available']);
        Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S-02', 'status' => 'occupied']);

        $response = $this->getJson('/api/seats/live-grid');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'summary' => [
                    'total' => 2,
                    'available' => 1,
                    'occupied' => 1,
                    'reserved' => 0,
                    'maintenance' => 0,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'Silent Zone' => [
                        '*' => ['id', 'zone_id', 'seat_number', 'status', 'zone'],
                    ],
                ],
            ]);
    }

    public function test_staff_can_update_seat_status(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $token = $staff->createToken('staff_token')->plainTextToken;

        $zone = Zone::create(['name' => 'Discussion Hall', 'code' => 'DH-01']);
        $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'D-01', 'status' => 'available']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/seats/{$seat->id}/status", [
                'status' => 'occupied',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Seat status updated successfully',
                'seat' => [
                    'id' => $seat->id,
                    'status' => 'occupied',
                ],
            ]);

        $this->assertDatabaseHas('seats', [
            'id' => $seat->id,
            'status' => 'occupied',
        ]);
    }

    public function test_regular_member_forbidden_from_updating_seat_status(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $token = $member->createToken('member_token')->plainTextToken;

        $zone = Zone::create(['name' => 'AC Hall', 'code' => 'AC-01']);
        $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'A-01', 'status' => 'available']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/seats/{$seat->id}/status", [
                'status' => 'occupied',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden: Staff privileges required.',
            ]);
    }

    public function test_unauthenticated_request_cannot_update_seat_status(): void
    {
        $zone = Zone::create(['name' => 'AC Hall', 'code' => 'AC-01']);
        $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'A-01', 'status' => 'available']);

        $response = $this->putJson("/api/seats/{$seat->id}/status", [
            'status' => 'occupied',
        ]);

        $response->assertStatus(401);
    }
}
