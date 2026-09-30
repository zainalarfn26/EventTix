<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Events\SeatStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredReservationJob implements ShouldQueue
{
    use Queueable;

    public int $reservationId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $reservationId)
    {
        $this->reservationId = $reservationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $reservation = Reservation::where('id', $this->reservationId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($reservation && now()->greaterThanOrEqualTo($reservation->expires_at)) {
                $reservation->update(['status' => 'expired']);

                Log::info("Reservation #{$reservation->id} expired and released seat #{$reservation->seat_id}.");

                // Broadcast available status back to channel
                event(new SeatStatusUpdated(
                    $reservation->event_id,
                    $reservation->seat_id,
                    $reservation->seat->seat_number ?? '',
                    'available'
                ));
            }
        });
    }
}
