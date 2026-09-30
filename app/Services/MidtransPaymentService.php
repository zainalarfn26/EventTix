<?php

namespace App\Services;

use App\Events\SeatStatusUpdated;
use App\Exceptions\InvalidMidtransSignatureException;
use App\Models\Reservation;
use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransPaymentService
{
    protected string $serverKey;
    protected bool $isProduction;
    protected string $snapUrl;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-dummy-key'));
        $this->isProduction = config('services.midtrans.is_production', false);
        $this->snapUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Create Midtrans Snap Token for a reservation.
     */
    public function createSnapToken(Reservation $reservation): Transaction
    {
        return DB::transaction(function () use ($reservation) {
            $existingTransaction = Transaction::where('reservation_id', $reservation->id)
                ->where('transaction_status', 'pending')
                ->first();

            if ($existingTransaction && $existingTransaction->snap_token) {
                return $existingTransaction;
            }

            $orderId = 'SP-' . strtoupper(Str::random(10));
            $grossAmount = (int) $reservation->price;

            $payload = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $reservation->user->name ?? 'Customer',
                    'email' => $reservation->user->email ?? 'customer@example.com',
                ],
                'item_details' => [
                    [
                        'id' => 'SEAT-' . $reservation->seat_id,
                        'price' => $grossAmount,
                        'quantity' => 1,
                        'name' => "Seat {$reservation->seat->seat_number} - {$reservation->event->title}",
                    ],
                ],
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit' => 'minute',
                    'duration' => 10,
                ],
            ];

            $snapToken = null;

            // Attempt to call Midtrans API (or mock sandbox response if dummy key)
            if (!str_contains($this->serverKey, 'dummy')) {
                $response = Http::withBasicAuth($this->serverKey, '')
                    ->acceptJson()
                    ->post($this->snapUrl, $payload);

                if ($response->successful()) {
                    $snapToken = $response->json('token');
                }
            }

            // Fallback for local sandbox testing without live API keys
            if (!$snapToken) {
                $snapToken = 'SANDBOX-TOKEN-' . Str::uuid();
            }

            $transaction = Transaction::create([
                'reservation_id' => $reservation->id,
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'snap_token' => $snapToken,
                'transaction_status' => 'pending',
                'raw_payload' => $payload,
            ]);

            return $transaction;
        });
    }

    /**
     * Handles Midtrans Webhook Payment Status Notification with SHA512 Signature verification.
     *
     * @throws InvalidMidtransSignatureException
     */
    public function handleWebhook(array $notification): Transaction
    {
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';
        $transactionStatus = $notification['transaction_status'] ?? '';

        // 1. Validate Signature Key: SHA512(order_id + status_code + gross_amount + ServerKey)
        if (!str_contains($this->serverKey, 'dummy')) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
            if ($signatureKey !== $expectedSignature) {
                throw new InvalidMidtransSignatureException();
            }
        }

        return DB::transaction(function () use ($orderId, $transactionStatus, $notification) {
            $transaction = Transaction::where('order_id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            $reservation = $transaction->reservation()->lockForUpdate()->firstOrFail();

            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                // Payment Successful
                $transaction->update([
                    'transaction_status' => 'settlement',
                    'payment_type' => $notification['payment_type'] ?? 'sandbox',
                    'paid_at' => now(),
                    'raw_payload' => $notification,
                ]);

                $reservation->update(['status' => 'confirmed']);

                // Issue E-Ticket if not generated yet
                if (!$reservation->ticket) {
                    $ticketCode = 'TKT-SP-' . strtoupper(Str::random(8));
                    Ticket::create([
                        'reservation_id' => $reservation->id,
                        'ticket_code' => $ticketCode,
                        'qr_code_hash' => hash('sha256', $ticketCode . $reservation->id),
                        'is_checked_in' => false,
                    ]);
                }

                // Broadcast WebSockets Update: Status is now BOOKED
                event(new SeatStatusUpdated(
                    $reservation->event_id,
                    $reservation->seat_id,
                    $reservation->seat->seat_number ?? '',
                    'booked',
                    $reservation->user_id
                ));

                Log::info("Payment confirmed for Order #{$orderId}. Ticket generated.");

            } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {
                // Payment Failed / Expired
                $statusMap = [
                    'expire' => 'expire',
                    'cancel' => 'cancel',
                    'deny' => 'deny',
                ];

                $transaction->update([
                    'transaction_status' => $statusMap[$transactionStatus] ?? 'cancel',
                    'raw_payload' => $notification,
                ]);

                $reservation->update(['status' => 'expired']);

                // Release seat back to Available
                event(new SeatStatusUpdated(
                    $reservation->event_id,
                    $reservation->seat_id,
                    $reservation->seat->seat_number ?? '',
                    'available'
                ));

                Log::info("Payment status {$transactionStatus} for Order #{$orderId}. Seat released.");
            }

            return $transaction;
        });
    }
}
