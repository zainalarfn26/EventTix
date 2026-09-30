<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketTier;
use App\Models\User;
use App\Services\MidtransPaymentService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_snap_token_and_transaction_for_order(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $user = User::where('email', 'customer@seatpulse.com')->first();
        $event = Event::first();
        $tier = TicketTier::where('event_id', $event->id)->first();

        $orderService = new OrderService();
        $order = $orderService->createOrder($user, $event->id, [
            ['ticket_tier_id' => $tier->id, 'quantity' => 1],
        ]);

        $paymentService = new MidtransPaymentService();
        $transaction = $paymentService->createSnapToken($order);

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->snap_token);
        $this->assertEquals('pending', $transaction->transaction_status);
    }

    public function test_handles_successful_webhook_payment_and_issues_tickets(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $user = User::where('email', 'customer@seatpulse.com')->first();
        $event = Event::first();
        $tier = TicketTier::where('event_id', $event->id)->first();

        $orderService = new OrderService();
        $order = $orderService->createOrder($user, $event->id, [
            ['ticket_tier_id' => $tier->id, 'quantity' => 2],
        ]);

        $paymentService = new MidtransPaymentService();
        $transaction = $paymentService->createSnapToken($order);

        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-dummy-key'));
        $signature = hash('sha512', $order->order_code . '200' . (string) $transaction->gross_amount . $serverKey);

        $notification = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => (string) $transaction->gross_amount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
        ];

        $updatedTransaction = $paymentService->handleWebhook($notification);

        $this->assertEquals('settlement', $updatedTransaction->transaction_status);
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertDatabaseCount('tickets', 2);
    }
}
