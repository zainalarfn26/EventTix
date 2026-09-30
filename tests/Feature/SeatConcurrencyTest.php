<?php

namespace Tests\Feature;

use App\Exceptions\SeatAlreadyBookedException;
use App\Models\Event;
use App\Models\Seat;
use App\Models\User;
use App\Services\SeatLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_successfully_lock_an_available_seat(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueAndSeatSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $user = User::where('email', 'customer@seatpulse.com')->first();
        $event = Event::first();
        $seat = Seat::first();

        $service = new SeatLockService();
        $reservation = $service->lockSeat($user, $event->id, $seat->id);

        $this->assertNotNull($reservation);
        $this->assertEquals('pending', $reservation->status);
        $this->assertEquals($user->id, $reservation->user_id);
        $this->assertEquals($seat->id, $reservation->seat_id);
    }

    public function test_prevents_double_booking_when_two_users_attempt_to_lock_the_same_seat(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueAndSeatSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $userA = User::where('email', 'customer@seatpulse.com')->first();
        $userB = User::factory()->create();

        $event = Event::first();
        $seat = Seat::first();

        $service = new SeatLockService();

        // User A locks first
        $reservationA = $service->lockSeat($userA, $event->id, $seat->id);
        $this->assertEquals('pending', $reservationA->status);

        // User B attempts to lock the same seat -> Expect Exception
        $this->expectException(SeatAlreadyBookedException::class);
        $service->lockSeat($userB, $event->id, $seat->id);

        // Database should only have 1 active pending reservation for this seat
        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseHas('reservations', [
            'user_id' => $userA->id,
            'seat_id' => $seat->id,
            'status' => 'pending',
        ]);
    }
}
