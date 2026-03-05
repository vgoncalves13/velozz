<?php

namespace App\Filament\Client\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class TenantSettings extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.client.pages.tenant-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('pages.tenant_settings.navigation');
    }

    public function getTitle(): string
    {
        return __('settings.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.configuration');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $this->form->fill([
            'name' => $tenant->name,
            'settings' => $tenant->settings ?? [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('settings.sections.company_information'))
                    ->icon('heroicon-o-building-office')
                    ->description(__('settings.sections.company_information_description'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('settings.labels.company_name'))
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('settings.logo')
                            ->label(__('settings.labels.logo'))
                            ->image()
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('tenant-logos'),

                        ColorPicker::make('settings.primary_color')
                            ->label(__('settings.labels.primary_color')),

                        ColorPicker::make('settings.secondary_color')
                            ->label(__('settings.labels.secondary_color')),
                    ])
                    ->columns(2),

                Section::make(__('settings.sections.business_hours'))
                    ->icon('heroicon-o-clock')
                    ->description(__('settings.sections.business_hours_description'))
                    ->schema([
                        TimePicker::make('settings.business_hours.start')
                            ->label(__('settings.labels.opening_time'))
                            ->seconds(false),

                        TimePicker::make('settings.business_hours.end')
                            ->label(__('settings.labels.closing_time'))
                            ->seconds(false),

                        Textarea::make('settings.business_hours.after_hours_message')
                            ->label(__('settings.labels.after_hours_message'))
                            ->helperText(__('settings.helper.after_hours_message'))
                            ->rows(3),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make(__('settings.sections.custom_fields'))
                    ->icon('heroicon-o-squares-plus')
                    ->description(__('settings.sections.custom_fields_description'))
                    ->schema([
                        Repeater::make('settings.custom_fields')
                            ->label('')
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('settings.labels.name'))
                                    ->required()
                                    ->maxLength(100),

                                Select::make('type')
                                    ->label(__('settings.labels.type'))
                                    ->required()
                                    ->options([
                                        'text' => __('settings.field_types.text'),
                                        'number' => __('settings.field_types.number'),
                                        'date' => __('settings.field_types.date'),
                                        'boolean' => __('settings.field_types.boolean'),
                                    ]),

                                TextInput::make('label')
                                    ->label(__('settings.labels.label'))
                                    ->helperText(__('settings.helper.display_label'))
                                    ->maxLength(100),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? __('settings.item_labels.new_field'))
                            ->addActionLabel(__('settings.actions.add_custom_field'))
                            ->defaultItems(0),
                    ])
                    ->collapsible(),

                Section::make(__('settings.sections.webhooks'))
                    ->icon('heroicon-o-arrow-up-right')
                    ->description(__('settings.sections.webhooks_description'))
                    ->schema([
                        Repeater::make('settings.webhooks')
                            ->label('')
                            ->schema([
                                TextInput::make('url')
                                    ->label(__('settings.labels.webhook_url'))
                                    ->url()
                                    ->required()
                                    ->maxLength(500)
                                    ->placeholder(__('settings.placeholders.webhook_url')),

                                Select::make('events')
                                    ->label(__('settings.labels.events_to_send'))
                                    ->multiple()
                                    ->required()
                                    ->options([
                                        'lead_created' => __('settings.webhook_events.lead_created'),
                                        'lead_updated' => __('settings.webhook_events.lead_updated'),
                                        'lead_transferred' => __('settings.webhook_events.lead_transferred'),
                                        'message_sent' => __('settings.webhook_events.message_sent'),
                                        'message_received' => __('settings.webhook_events.message_received'),
                                        'stage_changed' => __('settings.webhook_events.stage_changed'),
                                        'import_completed' => __('settings.webhook_events.import_completed'),
                                    ]),
                            ])
                            ->columns(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => parse_url($state['url'] ?? '', PHP_URL_HOST) ?? __('settings.item_labels.new_webhook'))
                            ->addActionLabel(__('settings.actions.add_webhook'))
                            ->defaultItems(0),
                    ])
                    ->collapsible(),

                Section::make(__('settings.sections.gdpr'))
                    ->icon('heroicon-o-shield-check')
                    ->description(__('settings.sections.gdpr_description'))
                    ->schema([
                        TextInput::make('settings.gdpr.anonymize_leads_inactive_months')
                            ->label(__('settings.labels.anonymize_leads'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->helperText(__('settings.helper.anonymize_leads')),

                        TextInput::make('settings.gdpr.delete_messages_after_months')
                            ->label(__('settings.labels.delete_messages'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->helperText(__('settings.helper.delete_messages')),

                        Textarea::make('settings.gdpr.consent_policy')
                            ->label(__('settings.labels.consent_policy'))
                            ->rows(4)
                            ->helperText(__('settings.helper.consent_policy')),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make(__('settings.sections.api_access'))
                    ->icon('heroicon-o-key')
                    ->description(__('settings.sections.api_access_description'))
                    ->schema([
                        TextInput::make('settings.api_key')
                            ->label(__('settings.labels.api_key'))
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(__('settings.helper.api_key')),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('settings.actions.save_settings'))
                ->submit('save'),
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('regenerate_api_key')
                ->label(__('settings.actions.regenerate_api_key'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(__('settings.modals.regenerate_api_key_heading'))
                ->modalDescription(__('settings.modals.regenerate_api_key_description'))
                ->action(function () {
                    $tenant = Filament::getTenant();
                    $settings = $tenant->settings ?? [];
                    $settings['api_key'] = 'vz_'.Str::random(40);

                    $tenant->update(['settings' => $settings]);

                    $this->form->fill([
                        'settings' => $settings,
                    ]);

                    Notification::make()
                        ->title(__('settings.notifications.api_key_regenerated_title'))
                        ->body(__('settings.notifications.api_key_regenerated_body'))
                        ->success()
                        ->send();
                }),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $tenant = Filament::getTenant();

            $tenant->update([
                'name' => $data['name'],
                'settings' => $data['settings'] ?? [],
            ]);

            Notification::make()
                ->title(__('settings.notifications.settings_saved'))
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
