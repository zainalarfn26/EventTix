<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venue = Venue::first();

        if ($venue) {
            Event::create([
                'venue_id' => $venue->id,
                'title' => 'Coldplay Music of the Spheres World Tour',
                'slug' => Str::slug('Coldplay Music of the Spheres World Tour'),
                'description' => 'Konser musik spektakuler real-time seating reservation arena.',
                'start_time' => now()->addDays(30)->setHour(19)->setMinute(0),
                'end_time' => now()->addDays(30)->setHour(23)->setMinute(0),
                'status' => 'published',
            ]);
        }
    }
}
