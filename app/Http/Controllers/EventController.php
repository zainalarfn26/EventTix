<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Reservation;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('venue')
            ->where('status', 'published')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $event->load(['venue.seats']);

        // Fetch active locked/booked reservations for this event
        $activeReservations = Reservation::where('event_id', $event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->keyBy('seat_id');

        return view('events.show', compact('event', 'activeReservations'));
    }
}
