<?php

namespace App\Console\Commands;

use App\Helpers\AuditHelper;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\WhatsAppMessage;
use Illuminate\Console\Command;

class GdprCleanup extends Command
{
    protected $signature = 'gdpr:cleanup';

    protected $description = 'Perform GDPR compliance cleanup: anonymize inactive leads and delete old messages';

    public function handle(): int
    {
        $this->info('Starting GDPR cleanup...');

        $tenants = Tenant::all();
        $this->info("Processing {$tenants->count()} tenants...");

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            $this->line("Processing tenant: {$tenant->name} (ID: {$tenant->id})");

            $config = $tenant->settings['gdpr'] ?? [];

            // Anonymize inactive leads
            if (isset($config['anonymize_leads_inactive_months'])) {
                $months = $config['anonymize_leads_inactive_months'];
                $this->info("  Anonymizing leads inactive for {$months} months...");

                $leads = Lead::where('tenant_id', $tenant->id)
                    ->where('updated_at', '<', now()->subMonths($months))
                    ->get();

                foreach ($leads as $lead) {
                    $lead->update([
                        'full_name' => 'ANONYMOUS',
                        'email' => null,
                        'phones' => null,
                        'whatsapps' => null,
                        'primary_whatsapp_index' => null,
                        'street_name' => null,
                        'number' => null,
                        'complement' => null,
                        'neighborhood' => null,
                        'district' => null,
                        'region' => null,
                        'city' => null,
                        'postal_code' => null,
                        'notes' => 'Anonymized due to GDPR compliance',
                    ]);

                    AuditHelper::log('gdpr_anonymization', 'lead', $lead->id);
                }

                $this->info("  Anonymized {$leads->count()} leads");
            }

            // Delete old messages
            if (isset($config['delete_messages_after_months'])) {
                $months = $config['delete_messages_after_months'];
                $this->info("  Deleting messages older than {$months} months...");

                $count = WhatsAppMessage::where('tenant_id', $tenant->id)
                    ->where('created_at', '<', now()->subMonths($months))
                    ->delete();

                $this->info("  Deleted {$count} messages");

                if ($count > 0) {
                    AuditHelper::log('gdpr_message_deletion', 'whatsapp_message', null, null, [
                        'count' => $count,
                        'months' => $months,
                    ]);
                }
            }

            if (empty($config)) {
                $this->line('  No GDPR settings configured');
            }

            tenancy()->end();
        }

        $this->info('GDPR cleanup completed successfully!');

        return Command::SUCCESS;
    }
}
