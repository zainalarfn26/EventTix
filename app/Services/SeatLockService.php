<?php

namespace App\Services;

use App\Events\SeatStatusUpdated;
use App\Exceptions\SeatAlreadyBookedException;
use App\Exceptions\SeatAlreadyLockedException;
use App\Jobs\ReleaseExpiredReservationJob;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeatLockService
{
    /**
     * Attempts to lock a seat for a user with high concurrency protection (Redis Mutex + DB Pessimistic Lock).
     *
     * @throws SeatAlreadyLockedException
     * @throws SeatAlreadyBookedException
     */
    public function lockSeat(User $user, int $eventId, int $seatId, int $holdDurationMinutes = 10): Reservation
    {
        $lockKey = "seat_lock_event_{$eventId}_seat_{$seatId}";
        
        // 1. Redis Distributed Mutex (Wait up to 3 seconds for lock, auto-expire lock key in 5s)
        $mutex = Cache::lock($lockKey, 5);

        if (!$mutex->get()) {
            throw new SeatAlreadyLockedException();
        }

        try {
            // 2. Database Transaction with Pessimistic Locking (lockForUpdate)
            return DB::transaction(function () use ($user, $eventId, $seatId, $holdDurationMinutes) {
                $event = Event::findOrFail($eventId);
                $seat = Seat::where('id', $seatId)
                    ->where('venue_id', $event->venue_id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check active reservation (pending or confirmed)
                $activeReservation = Reservation::where('event_id', $eventId)
                    ->where('seat_id', $seatId)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->lockForUpdate()
                    ->first();

                if ($activeReservation) {
                    // Check if pending reservation is already expired
                    if ($activeReservation->status === 'pending' && now()->greaterThanOrEqualTo($activeReservation->expires_at)) {
                        $activeReservation->update(['status' => 'expired']);
                    } else {
                        throw new SeatAlreadyBookedException();
                    }
                }

                $lockedAt = now();
                $expiresAt = $lockedAt->copy()->addMinutes($holdDurationMinutes);

                // Create Pending Reservation
                $reservation = Reservation::create([
                    'user_id' => $user->id,
                    'event_id' => $eventId,
                    'seat_id' => $seatId,
                    'status' => 'pending',
                    'price' => $seat->base_price,
                    'locked_at' => $lockedAt,
                    'expires_at' => $expiresAt,
                ]);

                // Dispatch Delayed Job to Release Seat if unpaid
                ReleaseExpiredReservationJob::dispatch($reservation->id)->delay($expiresAt);

                // Broadcast WebSockets Real-Time Update
                event(new SeatStatusUpdated(
                    $eventId,
                    $seatId,
                    $seat->seat_number,
                    'locked',
                    $user->id
                ));

                Log::info("User #{$user->id} locked seat #{$seatId} for Event #{$eventId} until {$expiresAt}.");

                return $reservation;
            });
        } finally {
            // Always release Redis mutex
            $mutex->release();
        }
    }

    /**
     * Explicitly cancel/release a user's pending seat reservation.
     */
    public function releaseSeat(User $user, int $reservationId): bool
    {
        return DB::transaction(function () use ($user, $reservationId) {
            $reservation = Reservation::where('id', $reservationId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$reservation) {
                return false;
            }

            $reservation->update(['status' => 'cancelled']);

            event(new SeatStatusUpdated(
                $reservation->event_id,
                $reservation->seat_id,
                $reservation->seat->seat_number ?? '',
                'available'
            ));

            return true;
        });
    }
}
