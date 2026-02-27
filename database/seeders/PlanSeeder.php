<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'price' => 29.00,
                'currency' => 'EUR',
                'leads_limit_per_month' => 500,
                'messages_limit_per_day' => 200,
                'operators_limit' => 2,
                'whatsapp_instances_limit' => 1,
                'trial_days' => 14,
            ],
            [
                'name' => 'Professional',
                'price' => 79.00,
                'currency' => 'EUR',
                'leads_limit_per_month' => 2000,
                'messages_limit_per_day' => 1000,
                'operators_limit' => 5,
                'whatsapp_instances_limit' => 2,
                'trial_days' => 14,
            ],
            [
                'name' => 'Enterprise',
                'price' => 199.00,
                'currency' => 'EUR',
                'leads_limit_per_month' => 10000,
                'messages_limit_per_day' => 5000,
                'operators_limit' => 20,
                'whatsapp_instances_limit' => 5,
                'trial_days' => 30,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['name' => $planData['name']],
                $planData
            );
        }
    }
}
