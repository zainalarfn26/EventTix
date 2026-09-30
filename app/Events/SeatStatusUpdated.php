<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $eventId;
    public int $seatId;
    public string $seatNumber;
    public string $status; // 'available', 'locked', 'booked'
    public ?int $lockedByUserId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $eventId, int $seatId, string $seatNumber, string $status, ?int $lockedByUserId = null)
    {
        $this->eventId = $eventId;
        $this->seatId = $seatId;
        $this->seatNumber = $seatNumber;
        $this->status = $status;
        $this->lockedByUserId = $lockedByUserId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("event.{$this->eventId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'seat.updated';
    }
}
