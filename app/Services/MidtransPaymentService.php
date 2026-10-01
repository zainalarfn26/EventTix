<?php

namespace App\Services;

use App\Exceptions\InvalidMidtransSignatureException;
use App\Models\Order;
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

    public function createCoreApiTransaction(Order $order, string $paymentType, ?string $bank = null): Transaction
    {
        return DB::transaction(function () use ($order, $paymentType, $bank) {
            $existingTransaction = Transaction::where('order_id', $order->id)
                ->where('transaction_status', 'pending')
                ->first();

            if ($existingTransaction) {
                return $existingTransaction; // Might need to recreate if they change payment method, but for now we keep it simple.
            }

            $order->load('items.ticketTier', 'user', 'event');
            $grossAmount = (int) $order->total_amount;
            
            $itemDetails = [];
            foreach ($order->items as $item) {
                $itemDetails[] = [
                    'id' => 'TIER-' . $item->ticket_tier_id,
                    'price' => (int) $item->unit_price,
                    'quantity' => $item->quantity,
                    'name' => substr("{$item->ticketTier->name} - {$order->event->title}", 0, 50),
                ];
            }

            if ($order->discount_amount > 0) {
                $itemDetails[] = [
                    'id' => 'PROMO-' . ($order->promo_code ?? 'DISCOUNT'),
                    'price' => -(int) $order->discount_amount,
                    'quantity' => 1,
                    'name' => substr("Discount Promo " . ($order->promo_code ?? ''), 0, 50),
                ];
            }

            $payload = [
                'payment_type' => $paymentType === 'va' ? 'bank_transfer' : $paymentType,
                'transaction_details' => [
                    'order_id' => $order->order_code,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name ?? 'Customer',
                    'email' => $order->user->email ?? 'customer@example.com',
                ],
                'item_details' => $itemDetails,
            ];

            if ($paymentType === 'va' && $bank) {
                $payload['bank_transfer'] = [
                    'bank' => $bank,
                ];
            }

            $coreApiUrl = $this->isProduction
                ? 'https://api.midtrans.com/v2/charge'
                : 'https://api.sandbox.midtrans.com/v2/charge';

            $rawPayloadResponse = null;

            if (!str_contains($this->serverKey, 'dummy')) {
                $response = Http::withBasicAuth($this->serverKey, '')
                    ->acceptJson()
                    ->post($coreApiUrl, $payload);

                if ($response->successful()) {
                    $rawPayloadResponse = $response->json();
                } else {
                    throw new \Exception('Midtrans Error: ' . $response->body());
                }
            } else {
                // Sandbox dummy response
                $rawPayloadResponse = [
                    'status_code' => '201',
                    'transaction_status' => 'pending',
                    'payment_type' => $paymentType === 'va' ? 'bank_transfer' : $paymentType,
                ];

                if ($paymentType === 'va') {
                    $rawPayloadResponse['va_numbers'] = [
                        [
                            'bank' => $bank,
                            'va_number' => '1234567890123'
                        ]
                    ];
                } else if ($paymentType === 'qris') {
                    $rawPayloadResponse['actions'] = [
                        [
                            'name' => 'generate-qr-code',
                            'method' => 'GET',
                            'url' => 'https://api.sandbox.midtrans.com/v2/qris/dummy.png'
                        ]
                    ];
                }
            }

            $transaction = Transaction::create([
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'gross_amount' => $grossAmount,
                'payment_type' => $paymentType === 'va' ? $bank : $paymentType,
                'transaction_status' => 'pending',
                'raw_payload' => $rawPayloadResponse,
            ]);

            return $transaction;
        });
    }

    /**
     * Create Midtrans Snap Token for an order.
     */
    public function createSnapToken(Order $order): Transaction
    {
        return DB::transaction(function () use ($order) {
            $existingTransaction = Transaction::where('order_id', $order->id)
                ->where('transaction_status', 'pending')
                ->first();

            if ($existingTransaction && $existingTransaction->snap_token) {
                return $existingTransaction;
            }

            $order->load('items.ticketTier', 'user', 'event');

            $grossAmount = (int) $order->total_amount;

            $itemDetails = [];
            foreach ($order->items as $item) {
                $itemDetails[] = [
                    'id' => 'TIER-' . $item->ticket_tier_id,
                    'price' => (int) $item->unit_price,
                    'quantity' => $item->quantity,
                    'name' => substr("{$item->ticketTier->name} - {$order->event->title}", 0, 50),
                ];
            }

            if ($order->discount_amount > 0) {
                $itemDetails[] = [
                    'id' => 'PROMO-' . ($order->promo_code ?? 'DISCOUNT'),
                    'price' => -(int) $order->discount_amount,
                    'quantity' => 1,
                    'name' => substr("Discount Promo " . ($order->promo_code ?? ''), 0, 50),
                ];
            }

            $payload = [
                'transaction_details' => [
                    'order_id' => $order->order_code,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name ?? 'Customer',
                    'email' => $order->user->email ?? 'customer@example.com',
                ],
                'item_details' => $itemDetails,
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
                'order_id' => $order->id,
                'order_code' => $order->order_code,
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
        $orderCode = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';
        $transactionStatus = $notification['transaction_status'] ?? '';

        // 1. Validate Signature Key: SHA512(order_id + status_code + gross_amount + ServerKey)
        if (!str_contains($this->serverKey, 'dummy')) {
            $expectedSignature = hash('sha512', $orderCode . $statusCode . $grossAmount . $this->serverKey);
            if ($signatureKey !== $expectedSignature) {
                throw new InvalidMidtransSignatureException();
            }
        }

        return DB::transaction(function () use ($orderCode, $transactionStatus, $notification) {
            $transaction = Transaction::where('order_code', $orderCode)
                ->lockForUpdate()
                ->firstOrFail();

            $order = $transaction->order()->lockForUpdate()->firstOrFail();

            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                // Payment Successful
                $transaction->update([
                    'transaction_status' => 'settlement',
                    'payment_type' => $notification['payment_type'] ?? 'sandbox',
                    'paid_at' => now(),
                    'raw_payload' => $notification,
                ]);

                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Generate individual e-tickets
                $this->generateTickets($order);

                Log::info("Payment confirmed for Order #{$orderCode}. Tickets generated.");

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

                // Release quota back to ticket tiers
                foreach ($order->items as $item) {
                    $tier = $item->ticketTier()->lockForUpdate()->first();
                    if ($tier) {
                        $tier->decrement('sold_count', $item->quantity);
                    }
                }

                $order->update(['status' => 'expired']);

                Log::info("Payment status {$transactionStatus} for Order #{$orderCode}. Quota released.");
            }

            return $transaction;
        });
    }

    /**
     * Verify order status directly via Midtrans API.
     * Useful for local development when webhooks cannot reach localhost.
     */
    public function verifyOrder(string $orderCode): Transaction
    {
        if (str_contains($this->serverKey, 'dummy')) {
            // If dummy key, just simulate success for testing
            $transaction = Transaction::where('order_code', $orderCode)->firstOrFail();
            return $this->handleWebhook([
                'order_id' => $orderCode,
                'status_code' => '200',
                'gross_amount' => $transaction->gross_amount,
                'transaction_status' => 'settlement',
                'payment_type' => 'sandbox',
            ]);
        }

        $apiUrl = $this->isProduction
            ? "https://api.midtrans.com/v2/{$orderCode}/status"
            : "https://api.sandbox.midtrans.com/v2/{$orderCode}/status";

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->get($apiUrl);

        if ($response->successful()) {
            return $this->handleWebhook($response->json());
        }

        throw new \Exception('Failed to verify order from Midtrans API.');
    }

    /**
     * Generate individual ticket records after payment confirmation.
     * Each purchased item generates N tickets (1 per quantity).
     */
    protected function generateTickets(Order $order): void
    {
        $order->load('items.ticketTier');

        foreach ($order->items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $ticketCode = 'TKT-SP-' . strtoupper(Str::random(8));
                Ticket::create([
                    'order_id' => $order->id,
                    'event_id' => $order->event_id,
                    'ticket_tier_id' => $item->ticket_tier_id,
                    'user_id' => $order->user_id,
                    'ticket_code' => $ticketCode,
                    'qr_code_hash' => hash('sha256', $ticketCode . $order->id . Str::random(8)),
                    'status' => 'active',
                ]);
            }
        }
    }
}
