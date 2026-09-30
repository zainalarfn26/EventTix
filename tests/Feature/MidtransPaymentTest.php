<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Seat;
use App\Models\User;
use App\Services\MidtransPaymentService;
use App\Services\SeatLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_snap_token_and_transaction_for_reservation(): void
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

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->snap_token);
        $this->assertEquals('pending', $transaction->transaction_status);
        $this->assertDatabaseHas('transactions', [
            'reservation_id' => $reservation->id,
            'transaction_status' => 'pending',
        ]);
    }

    public function test_handles_successful_webhook_payment_and_issues_ticket(): void
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

        // Simulate Webhook Notification from Midtrans Sandbox
        $notification = [
            'order_id' => $transaction->order_id,
            'status_code' => '200',
            'gross_amount' => (string) $transaction->gross_amount,
            'signature_key' => 'dummy_signature',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
        ];

        $updatedTransaction = $paymentService->handleWebhook($notification);

        $this->assertEquals('settlement', $updatedTransaction->transaction_status);
        $this->assertEquals('confirmed', $reservation->fresh()->status);
        $this->assertDatabaseHas('tickets', [
            'reservation_id' => $reservation->id,
            'is_checked_in' => false,
        ]);
    }
}
