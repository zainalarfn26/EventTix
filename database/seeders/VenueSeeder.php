<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Venue::create([
            'name' => 'Gelora Bung Karno Stadium',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Pintu Satu Senayan, Gelora, Tanah Abang, Jakarta Pusat',
            'capacity' => 80000,
        ]);

        Venue::create([
            'name' => 'JIExpo Kemayoran',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Benyamin Suaeb, Kemayoran, Jakarta Pusat',
            'capacity' => 10000,
        ]);

        Venue::create([
            'name' => 'ICE BSD',
            'city' => 'Tangerang Selatan',
            'address' => 'Jl. BSD Grand Boulevard, BSD City, Tangerang Selatan',
            'capacity' => 20000,
        ]);
    }
}
