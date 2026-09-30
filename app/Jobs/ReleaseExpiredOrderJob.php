<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\TicketTier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredOrderJob implements ShouldQueue
{
    use Queueable;

    public int $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job: release quota if order is still pending and expired.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $order = Order::where('id', $this->orderId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($order && now()->greaterThanOrEqualTo($order->expires_at)) {
                // Release quota back to ticket tiers
                foreach ($order->items as $item) {
                    $tier = TicketTier::where('id', $item->ticket_tier_id)
                        ->lockForUpdate()
                        ->first();

                    if ($tier) {
                        $tier->decrement('sold_count', $item->quantity);
                    }
                }

                $order->update(['status' => 'expired']);

                Log::info("Order #{$order->order_code} expired. Quota released.");
            }
        });
    }
}
