<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketTier;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventAdminController extends Controller
{
    public function index()
    {
        $events = Event::with(['venue', 'ticketTiers'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $venues = Venue::all();
        return view('admin.events.create', compact('venues'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|max:2048',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'required|in:draft,published,completed,cancelled',
            // Ticket Tiers
            'tiers' => 'required|array|min:1',
            'tiers.*.name' => 'required|string|max:50',
            'tiers.*.price' => 'required|numeric|min:0',
            'tiers.*.quota' => 'required|integer|min:1',
            'tiers.*.color' => 'required|string|max:7',
            'tiers.*.zone_label' => 'nullable|string|max:100',
            'tiers.*.wristband_color' => 'nullable|string|max:50',
            'tiers.*.description' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($validated['title']) . '-' . Str::random(5);

        $eventData = [
            'venue_id' => $validated['venue_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => $validated['status'],
        ];

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $eventData['banner_image'] = $request->file('banner_image')->store('events/banners', 'public');
        }

        $event = Event::create($eventData);

        // Create Ticket Tiers
        foreach ($validated['tiers'] as $index => $tierData) {
            TicketTier::create([
                'event_id' => $event->id,
                'name' => $tierData['name'],
                'price' => $tierData['price'],
                'quota' => $tierData['quota'],
                'color' => $tierData['color'],
                'zone_label' => $tierData['zone_label'] ?? null,
                'wristband_color' => $tierData['wristband_color'] ?? null,
                'description' => $tierData['description'] ?? null,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event baru berhasil ditambahkan dengan kelas tiket!');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus.');
    }
}
