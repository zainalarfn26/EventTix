<?php

namespace Tests\Feature;

use App\Exceptions\TicketSoldOutException;
use App\Models\Event;
use App\Models\TicketTier;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketQuotaConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_successfully_order_ticket_and_reserve_quota(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $user = User::where('email', 'customer@seatpulse.com')->first();
        $event = Event::first();
        $tier = TicketTier::where('event_id', $event->id)->first();

        $service = new OrderService();
        $order = $service->createOrder($user, $event->id, [
            ['ticket_tier_id' => $tier->id, 'quantity' => 2],
        ]);

        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(2, $order->items->sum('quantity'));
    }

    public function test_prevents_overselling_when_quota_is_insufficient(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $user = User::where('email', 'customer@seatpulse.com')->first();
        $event = Event::first();
        $tier = TicketTier::where('event_id', $event->id)->first();

        // Set quota to 1
        $tier->update(['quota' => 1]);

        $service = new OrderService();

        // User attempts to buy 2 tickets when only 1 left -> Expect TicketSoldOutException
        $this->expectException(TicketSoldOutException::class);
        $service->createOrder($user, $event->id, [
            ['ticket_tier_id' => $tier->id, 'quantity' => 2],
        ]);
    }
}
