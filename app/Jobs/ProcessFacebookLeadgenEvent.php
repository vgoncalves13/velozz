<?php

namespace App\Jobs;

use App\Enums\LeadActivityType;
use App\Enums\LeadSource;
use App\Models\FacebookLeadForm;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MetaAccount;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessFacebookLeadgenEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> Backoff in seconds between retries */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public string $leadgenId,
        public string $formId,
        public MetaAccount $metaAccount,
    ) {}

    public function handle(MetaGraphApiServiceInterface $metaApi): void
    {
        $form = FacebookLeadForm::withoutGlobalScopes()
            ->where('meta_account_id', $this->metaAccount->id)
            ->where('form_id', $this->formId)
            ->where('active', true)
            ->first();

        if (! $form) {
            Log::info('Lead Ads webhook: form not found or inactive, skipping', [
                'form_id' => $this->formId,
                'meta_account_id' => $this->metaAccount->id,
            ]);

            return;
        }

        // Early duplicate check before the API call
        $alreadyExists = Lead::withoutGlobalScopes()
            ->where('tenant_id', $this->metaAccount->tenant_id)
            ->whereJsonContains('custom_fields->facebook_lead_id', $this->leadgenId)
            ->whereNull('deleted_at')
            ->exists();

        if ($alreadyExists) {
            return;
        }

        $leadData = $metaApi->getLeadData($this->leadgenId, $this->metaAccount->access_token);

        if (empty($leadData)) {
            Log::warning('Lead Ads webhook: Meta API returned empty lead data', [
                'leadgen_id' => $this->leadgenId,
            ]);

            return;
        }

        $fields = collect($leadData['field_data'] ?? [])->pluck('values.0', 'name');
        $mapping = $form->field_mapping ?? [];

        $nameKey = $mapping['name'] ?? null;
        $emailKey = $mapping['email'] ?? null;
        $phoneKey = $mapping['phone'] ?? null;
        $whatsappKey = $mapping['whatsapp'] ?? null;

        $fullName = ($nameKey ? $fields->get($nameKey) : null)
            ?? $fields->get('full_name')
            ?? trim(($fields->get('first_name') ?? '').' '.($fields->get('last_name') ?? ''))
            ?: 'Lead sem nome';

        $email = ($emailKey ? $fields->get($emailKey) : null)
            ?? $fields->get('email')
            ?? $fields->get('email_address');

        $phoneNumber = ($phoneKey ? $fields->get($phoneKey) : null)
            ?? $fields->get('phone_number')
            ?? $fields->get('phone');

        $whatsappNumber = ($whatsappKey ? $fields->get($whatsappKey) : null);

        $lead = Lead::create([
            'tenant_id' => $this->metaAccount->tenant_id,
            'full_name' => $fullName,
            'email' => $email,
            'phones' => $phoneNumber ? [$phoneNumber] : null,
            'whatsapps' => $whatsappNumber ? [$whatsappNumber] : null,
            'source' => LeadSource::FacebookLeadAd,
            'consent_status' => 'pending',
            'custom_fields' => [
                'facebook_lead_id' => $this->leadgenId,
                'facebook_form_id' => $this->formId,
            ],
        ]);

        LeadActivity::create([
            'tenant_id' => $this->metaAccount->tenant_id,
            'lead_id' => $lead->id,
            'type' => LeadActivityType::Creation,
            'description' => 'Lead criado via Facebook Lead Ads (formulário: '.$form->form_name.')',
        ]);

        Log::info('Lead Ads webhook: lead created', [
            'lead_id' => $lead->id,
            'leadgen_id' => $this->leadgenId,
            'form_name' => $form->form_name,
        ]);
    }
}
