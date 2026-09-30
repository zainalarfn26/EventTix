<?php

namespace Tests\Feature;

use App\Models\Venue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_dashboard_and_see_analytics(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);

        $admin = User::where('email', 'admin@seatpulse.com')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin');
    }

    public function test_admin_can_create_new_event(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);

        $admin = User::where('email', 'admin@seatpulse.com')->first();
        $venue = Venue::first();

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'venue_id' => $venue->id,
            'title' => 'Bruno Mars Live in Jakarta',
            'description' => 'Konser musik spektakuler 2026',
            'start_time' => now()->addDays(10)->toDateTimeString(),
            'end_time' => now()->addDays(10)->addHours(4)->toDateTimeString(),
            'status' => 'published',
            'tiers' => [
                [
                    'name' => 'VIP',
                    'price' => 1500000,
                    'quota' => 500,
                    'color' => '#8b5cf6',
                    'zone_label' => 'Zona VIP',
                    'wristband_color' => 'Ungu',
                    'description' => 'Akses VIP',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseHas('events', [
            'title' => 'Bruno Mars Live in Jakarta',
            'venue_id' => $venue->id,
        ]);
    }
}
