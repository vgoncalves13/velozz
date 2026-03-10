<?php

namespace App\Jobs;

use App\Enums\LeadActivityType;
use App\Enums\LeadSource;
use App\Models\FacebookLeadForm;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncFacebookLeadFormLeads implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public FacebookLeadForm $form) {}

    public function handle(MetaGraphApiServiceInterface $metaApi): void
    {
        $form = $this->form->load('metaAccount.tenant');
        $metaAccount = $form->metaAccount;
        $tenant = $metaAccount->tenant;

        $response = $metaApi->getFormLeads($form->form_id, $metaAccount->access_token);
        $leads = $response['data'] ?? [];

        foreach ($leads as $leadData) {
            $this->createLeadFromData($leadData, $form, $tenant->id);
        }

        $form->update(['last_synced_at' => now()]);

        Log::info('Facebook Lead Ads: form synced', [
            'form_id' => $form->form_id,
            'form_name' => $form->form_name,
            'leads_synced' => count($leads),
        ]);
    }

    private function createLeadFromData(array $leadData, FacebookLeadForm $form, int $tenantId): void
    {
        $leadgenId = $leadData['id'] ?? null;

        if (! $leadgenId) {
            return;
        }

        $fields = collect($leadData['field_data'] ?? [])->pluck('values.0', 'name');

        $fullName = $fields->get('full_name')
            ?? trim(($fields->get('first_name') ?? '').' '.($fields->get('last_name') ?? ''))
            ?: null;

        $existing = Lead::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereJsonContains('custom_fields->facebook_lead_id', $leadgenId)
            ->exists();

        if ($existing) {
            return;
        }

        $lead = Lead::create([
            'tenant_id' => $tenantId,
            'full_name' => $fullName ?? 'Lead sem nome',
            'email' => $fields->get('email') ?? $fields->get('email_address'),
            'phone' => $fields->get('phone_number') ?? $fields->get('phone'),
            'source' => LeadSource::FacebookLeadAd,
            'consent_status' => 'pending',
            'custom_fields' => [
                'facebook_lead_id' => $leadgenId,
                'facebook_form_id' => $form->form_id,
            ],
        ]);

        LeadActivity::create([
            'tenant_id' => $tenantId,
            'lead_id' => $lead->id,
            'type' => LeadActivityType::Creation,
            'description' => 'Lead importado via Facebook Lead Ads (formulário: '.$form->form_name.')',
        ]);
    }
}
