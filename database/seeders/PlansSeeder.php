<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Starter',
            'price' => 49.00,
            'currency' => 'EUR',
            'leads_limit_per_month' => 1000,
            'messages_limit_per_day' => 500,
            'operators_limit' => 3,
            'whatsapp_instances_limit' => 1,
            'trial_days' => 14,
        ]);

        Plan::create([
            'name' => 'Professional',
            'price' => 99.00,
            'currency' => 'EUR',
            'leads_limit_per_month' => 5000,
            'messages_limit_per_day' => 2000,
            'operators_limit' => 10,
            'whatsapp_instances_limit' => 3,
            'trial_days' => 30,
        ]);

        Plan::create([
            'name' => 'Enterprise',
            'price' => 249.00,
            'currency' => 'EUR',
            'leads_limit_per_month' => 20000,
            'messages_limit_per_day' => 10000,
            'operators_limit' => 50,
            'whatsapp_instances_limit' => 10,
            'trial_days' => 30,
        ]);

        Plan::create([
            'name' => 'Unlimited',
            'price' => 499.00,
            'currency' => 'EUR',
            'leads_limit_per_month' => 999999,
            'messages_limit_per_day' => 999999,
            'operators_limit' => 999,
            'whatsapp_instances_limit' => 50,
            'trial_days' => 30,
        ]);
    }
}
