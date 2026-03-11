<?php

namespace App\Filament\Client\Pages;

use App\Enums\ClientNavigationGroup;
use App\Jobs\SyncFacebookLeadFormLeads;
use App\Models\FacebookLeadForm;
use App\Models\MetaAccount;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class MetaAccountSettings extends Page
{
    protected string $view = 'filament.client.pages.meta-account-settings';

    public bool $showMappingModal = false;

    public ?int $mappingFormDbId = null;

    public string $mappingFormName = '';

    /** @var array<int, array{key: string, label: string, type: string}> */
    public array $availableFormFields = [];

    public ?string $mappedNameField = null;

    public ?string $mappedEmailField = null;

    public ?string $mappedPhoneField = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('meta_settings.navigation');
    }

    public function getTitle(): string
    {
        return __('meta_settings.title');
    }

    protected static string|\UnitEnum|null $navigationGroup = ClientNavigationGroup::Integrations;

    public function getAccounts()
    {
        return MetaAccount::where('tenant_id', auth()->user()->tenant_id)->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connectInstagram')
                ->label(__('meta_settings.actions.connect_instagram'))
                ->icon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(fn () => route('instagram.oauth.redirect')),

            Action::make('connectFacebook')
                ->label(__('meta_settings.actions.connect_facebook'))
                ->icon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(fn () => route('meta.oauth.redirect')),

        ];
    }

    public function disconnect(int $accountId): void
    {
        $account = MetaAccount::where('id', $accountId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $account->update(['status' => 'disconnected']);

        Notification::make()
            ->title(__('meta_settings.notifications.account_disconnected_title'))
            ->success()
            ->send();
    }

    public function delete(int $accountId): void
    {
        $account = MetaAccount::where('id', $accountId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $account->delete();

        Notification::make()
            ->title(__('meta_settings.notifications.account_deleted_title'))
            ->success()
            ->send();
    }

    public function getLeadForms(int $metaAccountId): Collection
    {
        $account = MetaAccount::where('id', $metaAccountId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $apiService = app(\App\Services\Meta\MetaGraphApiServiceInterface::class);
        $response = $apiService->getPageLeadForms($account->page_id, $account->access_token);
        $apiForms = collect($response['data'] ?? []);

        $activeForms = FacebookLeadForm::where('meta_account_id', $metaAccountId)->pluck('form_id')->toArray();

        return $apiForms->map(fn (array $form) => array_merge($form, [
            'subscribed' => in_array($form['id'], $activeForms),
        ]));
    }

    public function toggleLeadForm(int $metaAccountId, string $formId, string $formName): void
    {
        $account = MetaAccount::where('id', $metaAccountId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $existing = FacebookLeadForm::where('meta_account_id', $metaAccountId)
            ->where('form_id', $formId)
            ->first();

        if ($existing) {
            $existing->delete();

            Notification::make()
                ->title(__('meta_settings.lead_forms.unsubscribed', ['name' => $formName]))
                ->success()
                ->send();

            return;
        }

        $form = FacebookLeadForm::create([
            'tenant_id' => $account->tenant_id,
            'meta_account_id' => $metaAccountId,
            'form_id' => $formId,
            'form_name' => $formName,
            'active' => true,
        ]);

        Notification::make()
            ->title(__('meta_settings.lead_forms.subscribed', ['name' => $formName]))
            ->success()
            ->send();

        $this->openMappingModal($form->id);
    }

    public function syncLeadForm(int $formId): void
    {
        $this->openMappingModal($formId);
    }

    public function openMappingModal(int $formDbId): void
    {
        $form = FacebookLeadForm::where('id', $formDbId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $metaAccount = $form->metaAccount;
        $metaApi = app(\App\Services\Meta\MetaGraphApiServiceInterface::class);
        $response = $metaApi->getFormQuestions($form->form_id, $metaAccount->access_token);

        $this->mappingFormDbId = $form->id;
        $this->mappingFormName = $form->form_name;
        $this->availableFormFields = $response['questions'] ?? [];

        $mapping = $form->field_mapping ?? [];
        $this->mappedNameField = $mapping['name'] ?? null;
        $this->mappedEmailField = $mapping['email'] ?? null;
        $this->mappedPhoneField = $mapping['phone'] ?? null;

        $this->showMappingModal = true;
    }

    public function saveMappingAndSync(): void
    {
        $form = FacebookLeadForm::where('id', $this->mappingFormDbId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $form->update([
            'field_mapping' => [
                'name' => $this->mappedNameField ?: null,
                'email' => $this->mappedEmailField ?: null,
                'phone' => $this->mappedPhoneField ?: null,
            ],
        ]);

        SyncFacebookLeadFormLeads::dispatch($form);

        $this->showMappingModal = false;

        Notification::make()
            ->title(__('meta_settings.lead_forms.mapping_saved'))
            ->success()
            ->send();
    }
}
