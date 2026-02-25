<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantsSeeder extends Seeder
{
    public function run(): void
    {
        // Create first demo tenant
        $tenant1 = Tenant::create([
            'name' => 'Demo Company 1',
            'slug' => 'demo1',
            'domain' => 'demo1.velozz.test',
            'status' => 'active',
            'plan_id' => null,
            'trial_ends_at' => now()->addDays(30),
            'admin_name' => 'John Admin',
            'admin_email' => 'admin@demo1.test',
            'admin_phone' => '+351912345678',
            'settings' => [
                'timezone' => 'Europe/Lisbon',
                'currency' => 'EUR',
            ],
        ]);

        Domain::create([
            'tenant_id' => $tenant1->id,
            'domain' => 'demo1.velozz.test',
        ]);

        // Create admin user for tenant1
        User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'John Admin',
            'email' => 'admin@demo1.test',
            'password' => Hash::make('password'),
            'phone' => '+351912345678',
            'role' => 'admin_client',
            'status' => 'active',
        ]);

        // Create second demo tenant
        $tenant2 = Tenant::create([
            'name' => 'Demo Company 2',
            'slug' => 'demo2',
            'domain' => 'demo2.velozz.test',
            'status' => 'trial',
            'plan_id' => null,
            'trial_ends_at' => now()->addDays(15),
            'admin_name' => 'Jane Manager',
            'admin_email' => 'admin@demo2.test',
            'admin_phone' => '+351923456789',
            'settings' => [
                'timezone' => 'Europe/Lisbon',
                'currency' => 'EUR',
            ],
        ]);

        Domain::create([
            'tenant_id' => $tenant2->id,
            'domain' => 'demo2.velozz.test',
        ]);

        // Create admin user for tenant2
        User::create([
            'tenant_id' => $tenant2->id,
            'name' => 'Jane Manager',
            'email' => 'admin@demo2.test',
            'password' => Hash::make('password'),
            'phone' => '+351923456789',
            'role' => 'admin_client',
            'status' => 'active',
        ]);
    }
}
