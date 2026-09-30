<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketTier;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gbk = Venue::where('name', 'like', '%Gelora%')->first();
        $jiexpo = Venue::where('name', 'like', '%JIExpo%')->first();
        $ice = Venue::where('name', 'like', '%ICE%')->first();

        // Event 1: Coldplay at GBK
        if ($gbk) {
            $event = Event::create([
                'venue_id' => $gbk->id,
                'title' => 'Coldplay Music of the Spheres World Tour',
                'slug' => 'coldplay-music-of-the-spheres-world-tour',
                'description' => 'Konser spektakuler Coldplay dengan pertunjukan visual futuristik yang memukau! Nikmati lagu-lagu hits seperti Yellow, Fix You, Viva La Vida, dan masih banyak lagi.',
                'start_time' => now()->addDays(30)->setHour(19)->setMinute(0),
                'end_time' => now()->addDays(30)->setHour(23)->setMinute(0),
                'status' => 'published',
            ]);

            $this->createTiers($event, [
                ['name' => 'VVIP', 'price' => 5000000, 'quota' => 200, 'color' => '#f59e0b', 'zone_label' => 'Zona Depan Panggung', 'wristband_color' => 'Emas', 'description' => 'Akses area terdekat dari panggung, welcome drink, merchandise eksklusif'],
                ['name' => 'VIP', 'price' => 2500000, 'quota' => 1000, 'color' => '#8b5cf6', 'zone_label' => 'Zona Tengah', 'wristband_color' => 'Ungu', 'description' => 'Area tribun tengah dengan pandangan sempurna ke panggung'],
                ['name' => 'CAT 1', 'price' => 1500000, 'quota' => 3000, 'color' => '#3b82f6', 'zone_label' => 'Zona Tribun Bawah', 'wristband_color' => 'Biru', 'description' => 'Tribun bawah dengan jarak dekat ke panggung'],
                ['name' => 'CAT 2', 'price' => 850000, 'quota' => 5000, 'color' => '#22c55e', 'zone_label' => 'Zona Tribun Atas', 'wristband_color' => 'Hijau', 'description' => 'Tribun atas dengan panorama luas arena konser'],
                ['name' => 'FESTIVAL', 'price' => 500000, 'quota' => 10000, 'color' => '#ef4444', 'zone_label' => 'Zona Festival (Berdiri)', 'wristband_color' => 'Merah', 'description' => 'Standing area di zona festival, bebas bergerak'],
            ]);
        }

        // Event 2: Ed Sheeran at JIExpo
        if ($jiexpo) {
            $event = Event::create([
                'venue_id' => $jiexpo->id,
                'title' => 'Ed Sheeran Mathematics Tour Jakarta',
                'slug' => 'ed-sheeran-mathematics-tour-jakarta',
                'description' => 'Ed Sheeran hadir di Jakarta dengan Mathematics Tour! Saksikan pertunjukan solo spektakuler dari penyanyi-penulis lagu terbaik dunia.',
                'start_time' => now()->addDays(45)->setHour(19)->setMinute(30),
                'end_time' => now()->addDays(45)->setHour(22)->setMinute(30),
                'status' => 'published',
            ]);

            $this->createTiers($event, [
                ['name' => 'VVIP', 'price' => 3500000, 'quota' => 100, 'color' => '#f59e0b', 'zone_label' => 'Golden Circle', 'wristband_color' => 'Emas', 'description' => 'Golden Circle — area terdepan dari panggung'],
                ['name' => 'VIP', 'price' => 2000000, 'quota' => 500, 'color' => '#8b5cf6', 'zone_label' => 'Zona Premium', 'wristband_color' => 'Ungu', 'description' => 'Zona premium dengan pandangan terbaik'],
                ['name' => 'REGULAR', 'price' => 950000, 'quota' => 2000, 'color' => '#3b82f6', 'zone_label' => 'Zona Reguler', 'wristband_color' => 'Biru', 'description' => 'Standing zone area reguler'],
            ]);
        }

        // Event 3: DWP at ICE BSD
        if ($ice) {
            $event = Event::create([
                'venue_id' => $ice->id,
                'title' => 'DWP (Djakarta Warehouse Project) 2026',
                'slug' => 'dwp-djakarta-warehouse-project-2026',
                'description' => 'Festival musik dance & elektronik terbesar di Asia Tenggara! Line-up DJ kelas dunia selama 2 hari penuh.',
                'start_time' => now()->addDays(60)->setHour(16)->setMinute(0),
                'end_time' => now()->addDays(61)->setHour(4)->setMinute(0),
                'status' => 'published',
            ]);

            $this->createTiers($event, [
                ['name' => 'VVIP', 'price' => 4500000, 'quota' => 300, 'color' => '#f59e0b', 'zone_label' => 'VVIP Lounge', 'wristband_color' => 'Emas', 'description' => 'VVIP Lounge area dengan fasilitas eksklusif, open bar, dan elevated viewing deck'],
                ['name' => 'VIP', 'price' => 2800000, 'quota' => 800, 'color' => '#8b5cf6', 'zone_label' => 'VIP Area', 'wristband_color' => 'Ungu', 'description' => 'Akses VIP area dengan bar priority dan rest area'],
                ['name' => 'PRESALE', 'price' => 1200000, 'quota' => 3000, 'color' => '#ec4899', 'zone_label' => 'General Admission', 'wristband_color' => 'Pink', 'description' => 'Early bird / presale general admission'],
                ['name' => 'REGULAR', 'price' => 1500000, 'quota' => 5000, 'color' => '#22c55e', 'zone_label' => 'General Admission', 'wristband_color' => 'Hijau', 'description' => 'General admission dengan akses ke semua stage'],
            ]);
        }
    }

    private function createTiers(Event $event, array $tiers): void
    {
        foreach ($tiers as $index => $tier) {
            TicketTier::create([
                'event_id' => $event->id,
                'name' => $tier['name'],
                'price' => $tier['price'],
                'quota' => $tier['quota'],
                'color' => $tier['color'],
                'zone_label' => $tier['zone_label'],
                'wristband_color' => $tier['wristband_color'],
                'description' => $tier['description'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
