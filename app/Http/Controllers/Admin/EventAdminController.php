<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketTier;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventAdminController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::with(['venue', 'ticketTiers'])
            ->withCount(['tickets as active_tickets_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $venues = Venue::orderBy('name')->get();
        return view('admin.events.create', compact('venues'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $eventData = [
            'venue_id' => $validated['venue_id'],
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(5),
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => $validated['status'],
        ];

        if ($request->hasFile('banner_image')) {
            $eventData['banner_image'] = $request->file('banner_image')->store('events/banners', 'public');
        }

        DB::transaction(function () use ($eventData, $validated) {
            $event = Event::create($eventData);

            foreach ($validated['tiers'] as $index => $tierData) {
                TicketTier::create($this->tierPayload($tierData) + [
                    'event_id' => $event->id,
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('admin.events.index')->with('success', 'Event baru berhasil ditambahkan dengan kelas tiket!');
    }

    public function edit(Event $event)
    {
        $event->load('ticketTiers');
        $venues = Venue::orderBy('name')->get();

        return view('admin.events.edit', compact('event', 'venues'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate($this->rules(true));

        $event->load('ticketTiers');
        $submittedIds = collect($validated['tiers'])->pluck('id')->filter()->map(fn ($id) => (int) $id);

        // Validate tier rules before touching the database
        foreach ($validated['tiers'] as $i => $tierData) {
            if (!empty($tierData['id'])) {
                $tier = $event->ticketTiers->firstWhere('id', (int) $tierData['id']);
                if (!$tier) {
                    return back()->withInput()->withErrors(["tiers.$i.name" => 'Kelas tiket tidak valid.']);
                }
                if ((int) $tierData['quota'] < $tier->sold_count) {
                    return back()->withInput()->withErrors([
                        "tiers.$i.quota" => "Kuota kelas {$tier->name} tidak boleh kurang dari tiket yang sudah terjual ({$tier->sold_count}).",
                    ]);
                }
            }
        }

        $toRemove = $event->ticketTiers->whereNotIn('id', $submittedIds);
        foreach ($toRemove as $tier) {
            if ($tier->sold_count > 0 || $tier->orderItems()->exists()) {
                return back()->withInput()->withErrors([
                    'tiers' => "Kelas {$tier->name} sudah memiliki pesanan dan tidak bisa dihapus. Nonaktifkan saja kelas tersebut.",
                ]);
            }
        }

        $eventData = [
            'venue_id' => $validated['venue_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => $validated['status'],
        ];

        $oldBanner = null;
        if ($request->hasFile('banner_image')) {
            $oldBanner = $event->banner_image;
            $eventData['banner_image'] = $request->file('banner_image')->store('events/banners', 'public');
        } elseif ($request->boolean('remove_banner')) {
            $oldBanner = $event->banner_image;
            $eventData['banner_image'] = null;
        }

        DB::transaction(function () use ($event, $eventData, $validated, $toRemove) {
            $event->update($eventData);

            foreach ($toRemove as $tier) {
                $tier->delete();
            }

            foreach ($validated['tiers'] as $index => $tierData) {
                $payload = $this->tierPayload($tierData) + [
                    'sort_order' => $index,
                    'is_active' => !empty($tierData['is_active']),
                ];

                if (!empty($tierData['id'])) {
                    $event->ticketTiers->firstWhere('id', (int) $tierData['id'])->update($payload);
                } else {
                    TicketTier::create($payload + ['event_id' => $event->id, 'is_active' => true]);
                }
            }
        });

        if ($oldBanner && Storage::disk('public')->exists($oldBanner)) {
            Storage::disk('public')->delete($oldBanner);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event "' . $event->title . '" berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Event $event)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,completed,cancelled',
        ]);

        $event->update(['status' => $validated['status']]);

        return back()->with('success', 'Status event diubah menjadi ' . strtoupper($validated['status']) . '.');
    }

    public function destroy(Event $event)
    {
        if ($event->orders()->where('status', 'paid')->exists()) {
            return redirect()->route('admin.events.index')
                ->with('error', 'Event ini sudah memiliki pesanan yang dibayar, sehingga tidak bisa dihapus. Ubah statusnya menjadi Cancelled atau Completed.');
        }

        $banner = $event->banner_image;
        $title = $event->title;
        $event->delete();

        if ($banner && Storage::disk('public')->exists($banner)) {
            Storage::disk('public')->delete($banner);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event "' . $title . '" berhasil dihapus.');
    }

    protected function rules(bool $updating = false): array
    {
        return [
            'venue_id' => 'required|exists:venues,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|max:2048',
            'remove_banner' => 'nullable|boolean',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'required|in:draft,published,completed,cancelled',
            'tiers' => 'required|array|min:1',
            'tiers.*.id' => 'nullable|integer',
            'tiers.*.name' => 'required|string|max:50',
            'tiers.*.price' => 'required|numeric|min:0',
            'tiers.*.quota' => 'required|integer|min:1',
            'tiers.*.color' => 'required|string|max:7',
            'tiers.*.zone_label' => 'nullable|string|max:100',
            'tiers.*.wristband_color' => 'nullable|string|max:50',
            'tiers.*.description' => 'nullable|string|max:255',
            'tiers.*.is_active' => 'nullable|boolean',
        ];
    }

    protected function tierPayload(array $tierData): array
    {
        return [
            'name' => $tierData['name'],
            'price' => $tierData['price'],
            'quota' => $tierData['quota'],
            'color' => $tierData['color'],
            'zone_label' => $tierData['zone_label'] ?? null,
            'wristband_color' => $tierData['wristband_color'] ?? null,
            'description' => $tierData['description'] ?? null,
        ];
    }
}
