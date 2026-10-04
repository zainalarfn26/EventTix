<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VenueAdminController extends Controller
{
    public function index(Request $request)
    {
        $venues = Venue::withCount('events')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('city', 'like', $term)->orWhere('address', 'like', $term));
            })
            ->orderBy('name')
            ->get();

        return view('admin.venues.index', compact('venues'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('layout_image')) {
            $data['layout_image'] = $request->file('layout_image')->store('venues/layouts', 'public');
        }
        unset($data['remove_layout']);

        Venue::create($data);

        return redirect()->route('admin.venues.index')->with('success', 'Venue "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Venue $venue)
    {
        $data = $this->validated($request);
        $old = $venue->layout_image;

        if ($request->hasFile('layout_image')) {
            $data['layout_image'] = $request->file('layout_image')->store('venues/layouts', 'public');
        } elseif ($request->boolean('remove_layout')) {
            $data['layout_image'] = null;
        } else {
            unset($data['layout_image']);
        }
        unset($data['remove_layout']);

        $venue->update($data);

        if ($old && array_key_exists('layout_image', $data) && $data['layout_image'] !== $old) {
            Storage::disk('public')->delete($old);
        }

        return redirect()->route('admin.venues.index')->with('success', 'Venue "' . $venue->name . '" berhasil diperbarui.');
    }

    public function destroy(Venue $venue)
    {
        if ($venue->events()->exists()) {
            return redirect()->route('admin.venues.index')
                ->with('error', 'Venue "' . $venue->name . '" masih dipakai oleh ' . $venue->events()->count() . ' event, sehingga tidak bisa dihapus.');
        }

        if ($venue->layout_image) {
            Storage::disk('public')->delete($venue->layout_image);
        }

        $name = $venue->name;
        $venue->delete();

        return redirect()->route('admin.venues.index')->with('success', 'Venue "' . $name . '" berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'capacity' => 'required|integer|min:0',
            'layout_image' => 'nullable|image|max:2048',
            'remove_layout' => 'nullable|boolean',
        ]);
    }
}
