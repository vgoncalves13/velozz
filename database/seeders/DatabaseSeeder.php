<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PlansSeeder::class,              // 1. Create plans first (referenced by tenants)
            RolesAndPermissionsSeeder::class, // 2. Create roles and permissions (needed by users)
            AdminMasterSeeder::class,         // 3. Create admin master user (needs roles)
            DemoTenantsSeeder::class,         // 4. Create demo tenants and users (needs plans and roles)
            PipelineStagesSeeder::class,
        ]);
    }
}
