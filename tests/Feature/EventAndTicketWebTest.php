<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\User;
use App\Services\MidtransPaymentService;
use App\Services\SeatLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventAndTicketWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_catalog_page_loads_successfully(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueAndSeatSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $response = $this->get(route('events.index'));

        $response->assertStatus(200);
        $response->assertSee('Coldplay Music of the Spheres World Tour');
    }

    public function test_event_seat_map_page_loads_successfully(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueAndSeatSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $event = Event::first();
        $response = $this->get(route('events.show', $event->slug));

        $response->assertStatus(200);
        $response->assertSee('MAIN STAGE / PANGGUNG UTAMA');
    }

    public function test_gate_scanner_validates_and_checks_in_ticket(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueAndSeatSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $user = User::where('email', 'customer@seatpulse.com')->first();
        $event = Event::first();
        $seat = Seat::first();

        $lockService = new SeatLockService();
        $reservation = $lockService->lockSeat($user, $event->id, $seat->id);

        $paymentService = new MidtransPaymentService();
        $transaction = $paymentService->createSnapToken($reservation);

        // Process successful payment to issue ticket
        $paymentService->handleWebhook([
            'order_id' => $transaction->order_id,
            'status_code' => '200',
            'gross_amount' => (string) $transaction->gross_amount,
            'signature_key' => 'dummy',
            'transaction_status' => 'settlement',
        ]);

        $ticket = Ticket::first();

        // 1. Scan ticket via API endpoint
        $response = $this->postJson(route('tickets.scan'), [
            'qr_code_hash' => $ticket->qr_code_hash,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertTrue($ticket->fresh()->is_checked_in);

        // 2. Scan ticket again (prevent double check-in)
        $secondResponse = $this->postJson(route('tickets.scan'), [
            'qr_code_hash' => $ticket->qr_code_hash,
        ]);

        $secondResponse->assertStatus(409);
        $secondResponse->assertJson([
            'success' => false,
        ]);
    }
}
