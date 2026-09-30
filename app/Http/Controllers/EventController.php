<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['venue', 'ticketTiers' => fn($q) => $q->where('is_active', true)])
            ->where('status', 'published')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $event->load([
            'venue',
            'ticketTiers' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ]);

        return view('events.show', compact('event'));
    }
}
