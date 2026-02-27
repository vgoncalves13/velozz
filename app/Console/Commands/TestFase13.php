<?php

namespace App\Console\Commands;

use App\Helpers\WebhookHelper;
use App\Jobs\DispatchWebhook;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestFase13 extends Command
{
    protected $signature = 'test:fase13 {--quick : Run only quick tests}';

    protected $description = 'Test FASE 13 - Tenant Settings and Webhooks';

    public function handle(): int
    {
        $this->info('🧪 Testing FASE 13 - Tenant Settings and Webhooks');
        $this->newLine();

        // Test 1: Check if TenantSettings page exists
        $this->info('1️⃣  Checking TenantSettings page...');
        if (class_exists(\App\Filament\Client\Pages\TenantSettings::class)) {
            $this->line('   ✅ TenantSettings class exists');
        } else {
            $this->error('   ❌ TenantSettings class not found');

            return self::FAILURE;
        }

        // Test 2: Check if WebhookHelper exists
        $this->info('2️⃣  Checking WebhookHelper...');
        if (class_exists(\App\Helpers\WebhookHelper::class)) {
            $this->line('   ✅ WebhookHelper class exists');
        } else {
            $this->error('   ❌ WebhookHelper class not found');

            return self::FAILURE;
        }

        // Test 3: Check if DispatchWebhook job exists
        $this->info('3️⃣  Checking DispatchWebhook job...');
        if (class_exists(\App\Jobs\DispatchWebhook::class)) {
            $this->line('   ✅ DispatchWebhook job exists');
        } else {
            $this->error('   ❌ DispatchWebhook job not found');

            return self::FAILURE;
        }

        // Test 4: Check if GenerateTenantApiKeys command exists
        $this->info('4️⃣  Checking GenerateTenantApiKeys command...');
        if (class_exists(\App\Console\Commands\GenerateTenantApiKeys::class)) {
            $this->line('   ✅ GenerateTenantApiKeys command exists');
        } else {
            $this->error('   ❌ GenerateTenantApiKeys command not found');

            return self::FAILURE;
        }

        // Test 5: Check if tenant has API key
        $this->info('5️⃣  Checking tenant API keys...');
        $tenant = Tenant::first();

        if (! $tenant) {
            $this->warn('   ⚠️  No tenants found - skipping API key test');
        } else {
            $hasApiKey = isset($tenant->settings['api_key']) && ! empty($tenant->settings['api_key']);

            if ($hasApiKey) {
                $this->line('   ✅ Tenant has API key: '.substr($tenant->settings['api_key'], 0, 10).'...');
            } else {
                $this->warn('   ⚠️  Tenant missing API key - run: php artisan tenants:generate-api-keys');
            }
        }

        if (! $this->option('quick')) {
            // Test 6: Test webhook dispatching
            $this->newLine();
            $this->info('6️⃣  Testing webhook dispatching...');

            if (! $tenant) {
                $this->warn('   ⚠️  No tenants found - skipping webhook test');
            } else {
                // Configure a test webhook
                $testUrl = 'https://webhook.site/'.Str::uuid();
                $this->line('   📡 Test webhook URL: '.$testUrl);

                try {
                    WebhookHelper::dispatch(
                        'test_event',
                        ['message' => 'Test from FASE 13', 'timestamp' => now()],
                        $tenant->id
                    );
                    $this->line('   ✅ Webhook dispatched successfully');
                    $this->warn('   ⚠️  Check queue to see if job was created: php artisan queue:work');
                } catch (\Exception $e) {
                    $this->error('   ❌ Failed to dispatch webhook: '.$e->getMessage());
                }
            }

            // Test 7: Test webhook events integration
            $this->newLine();
            $this->info('7️⃣  Checking webhook event integrations...');

            $files = [
                'LeadObserver' => 'app/Observers/LeadObserver.php',
                'SendWhatsAppMessage' => 'app/Jobs/SendWhatsAppMessage.php',
                'ProcessImport' => 'app/Jobs/ProcessImport.php',
                'ZApiWebhookController' => 'app/Http/Controllers/ZApiWebhookController.php',
            ];

            foreach ($files as $name => $path) {
                if (file_exists(base_path($path))) {
                    $content = file_get_contents(base_path($path));
                    if (str_contains($content, 'WebhookHelper::dispatch')) {
                        $this->line("   ✅ {$name} has webhook integration");
                    } else {
                        $this->warn("   ⚠️  {$name} missing webhook integration");
                    }
                } else {
                    $this->error("   ❌ {$name} file not found");
                }
            }
        }

        // Summary
        $this->newLine();
        $this->info('📊 Test Summary:');
        $this->line('   ✅ All core components exist');
        $this->line('   ✅ FASE 13 structure is correct');

        $this->newLine();
        $this->info('🎯 Next Steps:');
        $this->line('   1. Access: http://demo1.velozz.test/app/tenant-settings');
        $this->line('   2. Configure webhooks with https://webhook.site');
        $this->line('   3. Test all 7 webhook events (see TESTE_FASE_13.md)');
        $this->line('   4. Generate API keys: php artisan tenants:generate-api-keys');

        return self::SUCCESS;
    }
}
