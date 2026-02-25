<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminMasterSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'tenant_id' => null,
            'name' => 'Admin Master',
            'email' => 'admin@velozz.digital',
            'password' => Hash::make('password'),
            'phone' => null,
            'role' => 'admin_master',
            'status' => 'active',
        ]);
    }
}
