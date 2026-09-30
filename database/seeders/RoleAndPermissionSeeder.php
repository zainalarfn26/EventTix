<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'manage venues',
            'manage events',
            'manage seats',
            'reserve seats',
            'scan tickets',
            'view analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Create Roles and Assign Permissions
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo(Permission::all());

        $organizerRole = Role::findOrCreate('organizer', 'web');
        $organizerRole->givePermissionTo(['manage events', 'manage seats', 'scan tickets', 'view analytics']);

        $customerRole = Role::findOrCreate('customer', 'web');
        $customerRole->givePermissionTo(['reserve seats']);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@seatpulse.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create Organizer User
        $organizer = User::firstOrCreate(
            ['email' => 'organizer@seatpulse.com'],
            [
                'name' => 'Event Organizer',
                'password' => Hash::make('password123'),
            ]
        );
        $organizer->assignRole($organizerRole);

        // Create Customer User
        $customer = User::firstOrCreate(
            ['email' => 'customer@seatpulse.com'],
            [
                'name' => 'John Customer',
                'password' => Hash::make('password123'),
            ]
        );
        $customer->assignRole($customerRole);
    }
}
