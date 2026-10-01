<?php

namespace App\Services;

use App\Exceptions\TicketSoldOutException;
use App\Jobs\ReleaseExpiredOrderJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketTier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Create a new order with ticket tier items.
     * Uses DB pessimistic locking to prevent overselling.
     *
     * @param User $user
     * @param int $eventId
     * @param array $items Array of ['ticket_tier_id' => int, 'quantity' => int]
     * @param int $holdDurationMinutes
     * @return Order
     * @throws TicketSoldOutException
     */
    public function createOrder(User $user, int $eventId, array $items, int $holdDurationMinutes = 10, ?string $promoCode = null): Order
    {
        return DB::transaction(function () use ($user, $eventId, $items, $holdDurationMinutes, $promoCode) {
            $orderCode = 'SP-' . strtoupper(Str::random(10));
            $subTotalAmount = 0;
            $orderItems = [];

            foreach ($items as $item) {
                // Lock the ticket tier row to prevent race conditions (overselling)
                $tier = TicketTier::where('id', $item['ticket_tier_id'])
                    ->where('event_id', $eventId)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $available = $tier->quota - $tier->sold_count;
                $quantity = (int) $item['quantity'];

                if ($quantity > $available) {
                    throw new TicketSoldOutException(
                        "Tiket {$tier->name} tidak cukup. Tersisa {$available} tiket."
                    );
                }

                // Reserve the quota (increment sold_count)
                $tier->increment('sold_count', $quantity);

                $subtotal = $tier->price * $quantity;
                $subTotalAmount += $subtotal;

                $orderItems[] = [
                    'ticket_tier_id' => $tier->id,
                    'quantity' => $quantity,
                    'unit_price' => $tier->price,
                    'subtotal' => $subtotal,
                ];
            }

            $discountAmount = 0;
            if ($promoCode) {
                $promo = \App\Models\Promo::where('code', $promoCode)->lockForUpdate()->first();
                if ($promo && $promo->isValid()) {
                    $discountAmount = $promo->calculateDiscount($subTotalAmount);
                    $promo->increment('current_usages');
                } else {
                    $promoCode = null; // invalid promo code
                }
            }

            $totalAmount = max(0, $subTotalAmount - $discountAmount);
            $expiresAt = now()->addMinutes($holdDurationMinutes);

            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $eventId,
                'order_code' => $orderCode,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'promo_code' => $promoCode,
                'status' => 'pending',
                'expires_at' => $expiresAt,
            ]);

            foreach ($orderItems as $orderItem) {
                OrderItem::create(array_merge($orderItem, ['order_id' => $order->id]));
            }

            // Dispatch delayed job to release order if unpaid after hold duration
            ReleaseExpiredOrderJob::dispatch($order->id)->delay($expiresAt);

            Log::info("User #{$user->id} created Order #{$order->order_code} for Event #{$eventId}. Expires at {$expiresAt}.");

            return $order->load('items.ticketTier');
        });
    }

    /**
     * Cancel a pending order and release the ticket quota.
     */
    public function cancelOrder(User $user, int $orderId): bool
    {
        return DB::transaction(function () use ($user, $orderId) {
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return false;
            }

            // Release quota back to ticket tiers
            foreach ($order->items as $item) {
                $tier = TicketTier::where('id', $item->ticket_tier_id)
                    ->lockForUpdate()
                    ->first();

                if ($tier) {
                    $tier->decrement('sold_count', $item->quantity);
                }
            }

            $order->update(['status' => 'cancelled']);

            Log::info("Order #{$order->order_code} cancelled. Quota released.");

            return true;
        });
    }
}
