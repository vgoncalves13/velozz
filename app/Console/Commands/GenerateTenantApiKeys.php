<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateTenantApiKeys extends Command
{
    protected $signature = 'tenants:generate-api-keys {--force : Regenerate API keys even if they already exist}';

    protected $description = 'Generate API keys for tenants that don\'t have one';

    public function handle(): int
    {
        $force = $this->option('force');

        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->info('No tenants found.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;

        foreach ($tenants as $tenant) {
            $settings = $tenant->settings ?? [];
            $hasApiKey = isset($settings['api_key']) && ! empty($settings['api_key']);

            if ($hasApiKey && ! $force) {
                $skipped++;

                continue;
            }

            $settings['api_key'] = 'vz_'.Str::random(40);
            $tenant->update(['settings' => $settings]);

            $generated++;
            $this->info("API key generated for tenant: {$tenant->name}");
        }

        $this->newLine();
        $this->info("Generated: {$generated}");
        $this->info("Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
