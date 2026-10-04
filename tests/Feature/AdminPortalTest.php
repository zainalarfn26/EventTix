<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\Promo;
use App\Models\Ticket;
use App\Models\TicketTier;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\VenueSeeder::class);
        $this->seed(\Database\Seeders\EventSeeder::class);
    }

    protected function getAdmin(): User
    {
        return User::where('email', 'admin@seatpulse.com')->first();
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_all_admin_pages(): void
    {
        $admin = $this->getAdmin();

        $routes = [
            route('admin.dashboard'),
            route('admin.events.index'),
            route('admin.events.create'),
            route('admin.venues.index'),
            route('admin.promos.index'),
            route('admin.accounts.index'),
            route('admin.tickets.index'),
            route('admin.finances.index'),
            route('admin.reports.index'),
            route('admin.settings'),
            route('admin.search', ['q' => 'Jakarta']),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($admin)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_admin_can_create_edit_and_delete_event(): void
    {
        $admin = $this->getAdmin();
        $venue = Venue::first();

        // 1. Create
        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'venue_id' => $venue->id,
            'title' => 'Coldplay Live in Jakarta 2026',
            'description' => 'Konser musik spektakuler',
            'start_time' => now()->addDays(20)->toDateTimeString(),
            'end_time' => now()->addDays(20)->addHours(4)->toDateTimeString(),
            'status' => 'published',
            'tiers' => [
                [
                    'name' => 'VIP',
                    'price' => 2000000,
                    'quota' => 100,
                    'color' => '#8b5cf6',
                    'zone_label' => 'Zona Depan',
                    'wristband_color' => 'Ungu',
                    'description' => 'Akses VIP',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseHas('events', ['title' => 'Coldplay Live in Jakarta 2026']);

        $event = Event::where('title', 'Coldplay Live in Jakarta 2026')->first();
        $tier = $event->ticketTiers->first();

        // 2. Edit Page
        $editRes = $this->actingAs($admin)->get(route('admin.events.edit', $event));
        $editRes->assertStatus(200);
        $editRes->assertSee('Coldplay Live in Jakarta 2026');

        // 3. Update
        $updateRes = $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'venue_id' => $venue->id,
            'title' => 'Coldplay Live in Jakarta 2026 (Updated)',
            'description' => 'Deskripsi baru',
            'start_time' => now()->addDays(21)->toDateTimeString(),
            'end_time' => now()->addDays(21)->addHours(4)->toDateTimeString(),
            'status' => 'published',
            'tiers' => [
                [
                    'id' => $tier->id,
                    'name' => 'VIP Gold',
                    'price' => 2500000,
                    'quota' => 150,
                    'color' => '#f59e0b',
                    'zone_label' => 'Zona Depan Gold',
                    'wristband_color' => 'Emas',
                    'description' => 'Akses VIP Gold',
                    'is_active' => '1',
                ],
            ],
        ]);
        $updateRes->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseHas('events', ['title' => 'Coldplay Live in Jakarta 2026 (Updated)']);

        // 4. Quick Status Toggle
        $statusRes = $this->actingAs($admin)->patch(route('admin.events.status', $event), [
            'status' => 'draft',
        ]);
        $statusRes->assertSessionHas('success');
        $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => 'draft']);

        // 5. Delete
        $delRes = $this->actingAs($admin)->delete(route('admin.events.destroy', $event));
        $delRes->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_admin_can_manage_accounts_crud(): void
    {
        $admin = $this->getAdmin();

        // 1. Create User
        $createRes = $this->actingAs($admin)->post(route('admin.accounts.store'), [
            'name' => 'Budi Organizer',
            'email' => 'budi@seatpulse.com',
            'password' => 'secret12345',
            'role' => 'organizer',
        ]);
        $createRes->assertRedirect(route('admin.accounts.index'));
        $this->assertDatabaseHas('users', ['email' => 'budi@seatpulse.com']);

        $budi = User::where('email', 'budi@seatpulse.com')->first();
        $this->assertTrue($budi->hasRole('organizer'));

        // 2. Update User (Change role to customer, update name)
        $updateRes = $this->actingAs($admin)->put(route('admin.accounts.update', $budi), [
            'name' => 'Budi Santoso',
            'email' => 'budi@seatpulse.com',
            'password' => 'newpassword123',
            'role' => 'customer',
        ]);
        $updateRes->assertRedirect(route('admin.accounts.index'));
        $this->assertDatabaseHas('users', ['id' => $budi->id, 'name' => 'Budi Santoso']);
        $budi->refresh();
        $this->assertTrue($budi->hasRole('customer'));
        $this->assertTrue(Hash::check('newpassword123', $budi->password));

        // 3. Delete User
        $deleteRes = $this->actingAs($admin)->delete(route('admin.accounts.destroy', $budi));
        $deleteRes->assertRedirect(route('admin.accounts.index'));
        $this->assertDatabaseMissing('users', ['id' => $budi->id]);
    }

    public function test_admin_can_manage_venues(): void
    {
        $admin = $this->getAdmin();

        // 1. Create Venue
        $res = $this->actingAs($admin)->post(route('admin.venues.store'), [
            'name' => 'Stadion Madya GBK',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Pintu Satu Senayan',
            'capacity' => 9000,
        ]);
        $res->assertRedirect(route('admin.venues.index'));
        $this->assertDatabaseHas('venues', ['name' => 'Stadion Madya GBK']);

        $venue = Venue::where('name', 'Stadion Madya GBK')->first();

        // 2. Update Venue
        $upRes = $this->actingAs($admin)->put(route('admin.venues.update', $venue), [
            'name' => 'Stadion Madya GBK Senayan',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Pintu Satu Senayan No. 1',
            'capacity' => 10000,
        ]);
        $upRes->assertRedirect(route('admin.venues.index'));
        $this->assertDatabaseHas('venues', ['name' => 'Stadion Madya GBK Senayan', 'capacity' => 10000]);

        // 3. Delete Venue
        $delRes = $this->actingAs($admin)->delete(route('admin.venues.destroy', $venue));
        $delRes->assertRedirect(route('admin.venues.index'));
        $this->assertDatabaseMissing('venues', ['id' => $venue->id]);
    }

    public function test_admin_can_manage_promos(): void
    {
        $admin = $this->getAdmin();

        // 1. Create Promo
        $res = $this->actingAs($admin)->post(route('admin.promos.store'), [
            'code' => 'DISC25',
            'discount_type' => 'percentage',
            'amount' => 25,
            'max_usages' => 50,
            'is_active' => '1',
        ]);
        $res->assertRedirect(route('admin.promos.index'));
        $this->assertDatabaseHas('promos', ['code' => 'DISC25', 'amount' => 25]);

        $promo = Promo::where('code', 'DISC25')->first();

        // 2. Toggle Status
        $this->actingAs($admin)->patch(route('admin.promos.toggle', $promo));
        $this->assertDatabaseHas('promos', ['id' => $promo->id, 'is_active' => false]);

        // 3. Update Promo
        $upRes = $this->actingAs($admin)->put(route('admin.promos.update', $promo), [
            'code' => 'DISC30',
            'discount_type' => 'percentage',
            'amount' => 30,
            'max_usages' => 100,
            'is_active' => '1',
        ]);
        $upRes->assertRedirect(route('admin.promos.index'));
        $this->assertDatabaseHas('promos', ['code' => 'DISC30', 'amount' => 30]);

        $promo = Promo::where('code', 'DISC30')->first();

        // 4. Delete Promo
        $delRes = $this->actingAs($admin)->delete(route('admin.promos.destroy', $promo));
        $delRes->assertRedirect(route('admin.promos.index'));
        $this->assertDatabaseMissing('promos', ['id' => $promo->id]);
    }

    public function test_admin_can_manage_tickets_and_check_in(): void
    {
        $admin = $this->getAdmin();
        $event = Event::first();
        $tier = $event->ticketTiers->first();
        $customer = User::where('email', 'customer@seatpulse.com')->first();

        $order = Order::create([
            'user_id' => $customer->id,
            'event_id' => $event->id,
            'order_code' => 'SP-TEST1234',
            'total_amount' => $tier->price,
            'status' => 'paid',
            'expires_at' => now()->addHour(),
            'paid_at' => now(),
        ]);

        $ticket = Ticket::create([
            'order_id' => $order->id,
            'event_id' => $event->id,
            'ticket_tier_id' => $tier->id,
            'user_id' => $customer->id,
            'ticket_code' => 'TIX-TEST999',
            'qr_code_hash' => 'dummyhash999',
            'status' => 'active',
        ]);

        // 1. Manual check-in
        $this->actingAs($admin)->post(route('admin.tickets.check_in', $ticket));
        $ticket->refresh();
        $this->assertEquals('checked_in', $ticket->status);
        $this->assertNotNull($ticket->checked_in_at);

        // 2. Undo check-in
        $this->actingAs($admin)->post(route('admin.tickets.undo_check_in', $ticket));
        $ticket->refresh();
        $this->assertEquals('active', $ticket->status);
        $this->assertNull($ticket->checked_in_at);

        // 3. Cancel ticket
        $this->actingAs($admin)->post(route('admin.tickets.cancel', $ticket));
        $ticket->refresh();
        $this->assertEquals('cancelled', $ticket->status);

        // 4. Reactivate ticket
        $this->actingAs($admin)->post(route('admin.tickets.reactivate', $ticket));
        $ticket->refresh();
        $this->assertEquals('active', $ticket->status);
    }

    public function test_admin_settings_update_profile_and_password(): void
    {
        $admin = $this->getAdmin();

        // 1. Update Profile
        $res = $this->actingAs($admin)->put(route('admin.settings.profile'), [
            'name' => 'Super Administrator',
            'email' => 'admin@seatpulse.com',
        ]);
        $res->assertRedirect(route('admin.settings'));
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Super Administrator']);

        // 2. Update Password
        $pwRes = $this->actingAs($admin)->put(route('admin.settings.password'), [
            'current_password' => 'password123',
            'password' => 'newadminpass123',
            'password_confirmation' => 'newadminpass123',
        ]);
        $pwRes->assertRedirect(route('admin.settings'));
        $admin->refresh();
        $this->assertTrue(Hash::check('newadminpass123', $admin->password));
    }
}
