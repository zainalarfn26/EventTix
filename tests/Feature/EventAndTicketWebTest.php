<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketTier;
use App\Models\Ticket;
use App\Models\User;
use App\Services\MidtransPaymentService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventAndTicketWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_catalog_page_loads_successfully(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $response = $this->get(route('events.index'));

        $response->assertStatus(200);
        $response->assertSee('Coldplay Music of the Spheres World Tour');
    }

    public function test_event_detail_page_loads_successfully(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $event = Event::first();
        $response = $this->get(route('events.show', $event->slug));

        $response->assertStatus(200);
        $response->assertSee('Coldplay Music of the Spheres World Tour');
    }

    public function test_gate_scanner_validates_and_checks_in_ticket(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $user = User::where('email', 'customer@seatpulse.com')->first();
        $admin = User::where('email', 'admin@seatpulse.com')->first();
        $event = Event::first();
        $tier = TicketTier::where('event_id', $event->id)->first();

        $orderService = new OrderService();
        $order = $orderService->createOrder($user, $event->id, [
            ['ticket_tier_id' => $tier->id, 'quantity' => 1],
        ]);

        $paymentService = new MidtransPaymentService();
        $transaction = $paymentService->createSnapToken($order);

        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-dummy-key'));
        $signature = hash('sha512', $order->order_code . '200' . (string) $transaction->gross_amount . $serverKey);

        $paymentService->handleWebhook([
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => (string) $transaction->gross_amount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ]);

        $ticket = Ticket::first();

        // 1. Scan ticket via API endpoint (authenticated as admin)
        $response = $this->actingAs($admin)->postJson(route('tickets.scan'), [
            'qr_code_hash' => $ticket->qr_code_hash,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertEquals('checked_in', $ticket->fresh()->status);

        // 2. Scan ticket again (prevent double check-in)
        $secondResponse = $this->actingAs($admin)->postJson(route('tickets.scan'), [
            'qr_code_hash' => $ticket->qr_code_hash,
        ]);

        $secondResponse->assertStatus(409);
        $secondResponse->assertJson([
            'success' => false,
        ]);
    }
}
