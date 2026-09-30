<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\Seat;
use Illuminate\Database\Seeder;

class VenueAndSeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venue = Venue::create([
            'name' => 'JIExpo Grand Ballroom',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. H. R. Rasuna Said, Kemayoran, Jakarta Pusat',
            'capacity' => 100,
            'layout_config' => [
                'rows' => ['A', 'B', 'C', 'D', 'E'],
                'cols_per_row' => 10,
                'categories' => [
                    'VVIP' => ['rows' => ['A'], 'price' => 2500000],
                    'VIP' => ['rows' => ['B', 'C'], 'price' => 1500000],
                    'REGULAR' => ['rows' => ['D', 'E'], 'price' => 750000],
                ],
            ],
        ]);

        $rows = ['A', 'B', 'C', 'D', 'E'];

        foreach ($rows as $row) {
            $category = 'REGULAR';
            $price = 750000;

            if ($row === 'A') {
                $category = 'VVIP';
                $price = 2500000;
            } elseif (in_array($row, ['B', 'C'])) {
                $category = 'VIP';
                $price = 1500000;
            }

            for ($col = 1; $col <= 10; $col++) {
                Seat::create([
                    'venue_id' => $venue->id,
                    'seat_number' => "{$row}-{$col}",
                    'row' => $row,
                    'column' => $col,
                    'category' => $category,
                    'base_price' => $price,
                    'is_active' => true,
                ]);
            }
        }
    }
}
