<?php

namespace App\Filament\Client\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
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

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;

        $this->form->fill([
            'name' => $tenant->name,
            'settings' => $tenant->settings ?? [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Company Information')
                    ->icon('heroicon-o-building-office')
                    ->description('Configure your company details and branding')
                    ->schema([
                        TextInput::make('name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('settings.logo')
                            ->label('Logo')
                            ->image()
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('tenant-logos'),

                        ColorPicker::make('settings.primary_color')
                            ->label('Primary Color'),

                        ColorPicker::make('settings.secondary_color')
                            ->label('Secondary Color'),
                    ])
                    ->columns(2),

                Section::make('Business Hours')
                    ->icon('heroicon-o-clock')
                    ->description('Set your customer service hours')
                    ->schema([
                        TimePicker::make('settings.business_hours.start')
                            ->label('Opening Time')
                            ->seconds(false),

                        TimePicker::make('settings.business_hours.end')
                            ->label('Closing Time')
                            ->seconds(false),

                        Textarea::make('settings.business_hours.after_hours_message')
                            ->label('After Hours Message')
                            ->helperText('Message sent when contact is made outside business hours')
                            ->rows(3),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Custom Fields')
                    ->icon('heroicon-o-squares-plus')
                    ->description('Add custom fields to your leads')
                    ->schema([
                        Repeater::make('settings.custom_fields')
                            ->label('')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(100),

                                Select::make('type')
                                    ->required()
                                    ->options([
                                        'text' => 'Text',
                                        'number' => 'Number',
                                        'date' => 'Date',
                                        'boolean' => 'Yes/No',
                                    ]),

                                TextInput::make('label')
                                    ->helperText('Display label (optional, uses name if empty)')
                                    ->maxLength(100),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'New Field')
                            ->addActionLabel('Add Custom Field')
                            ->defaultItems(0),
                    ])
                    ->collapsible(),

                Section::make('Outbound Webhooks')
                    ->icon('heroicon-o-arrow-up-right')
                    ->description('Configure webhooks to receive notifications about events')
                    ->schema([
                        Repeater::make('settings.webhooks')
                            ->label('')
                            ->schema([
                                TextInput::make('url')
                                    ->label('Webhook URL')
                                    ->url()
                                    ->required()
                                    ->maxLength(500)
                                    ->placeholder('https://your-domain.com/webhook'),

                                Select::make('events')
                                    ->label('Events to Send')
                                    ->multiple()
                                    ->required()
                                    ->options([
                                        'lead_created' => 'Lead Created',
                                        'lead_updated' => 'Lead Updated',
                                        'lead_transferred' => 'Lead Transferred',
                                        'message_sent' => 'Message Sent',
                                        'message_received' => 'Message Received',
                                        'stage_changed' => 'Pipeline Stage Changed',
                                        'import_completed' => 'Import Completed',
                                    ]),
                            ])
                            ->columns(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => parse_url($state['url'] ?? '', PHP_URL_HOST) ?? 'New Webhook')
                            ->addActionLabel('Add Webhook')
                            ->defaultItems(0),
                    ])
                    ->collapsible(),

                Section::make('GDPR Compliance')
                    ->icon('heroicon-o-shield-check')
                    ->description('Configure data retention and privacy settings')
                    ->schema([
                        TextInput::make('settings.gdpr.anonymize_leads_inactive_months')
                            ->label('Anonymize Inactive Leads After (months)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->helperText('Leads not updated for X months will be anonymized'),

                        TextInput::make('settings.gdpr.delete_messages_after_months')
                            ->label('Delete Messages After (months)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->helperText('WhatsApp messages older than X months will be deleted'),

                        Textarea::make('settings.gdpr.consent_policy')
                            ->label('Consent Policy Text')
                            ->rows(4)
                            ->helperText('Text shown to users about data consent'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('API Access')
                    ->icon('heroicon-o-key')
                    ->description('Manage your API credentials')
                    ->schema([
                        TextInput::make('settings.api_key')
                            ->label('API Key')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Use this key to authenticate API requests'),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('regenerate_api_key')
                ->label('Regenerate API Key')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Regenerate API Key')
                ->modalDescription('This will invalidate your current API key. Any integrations using the old key will stop working.')
                ->action(function () {
                    $tenant = auth()->user()->tenant;
                    $settings = $tenant->settings ?? [];
                    $settings['api_key'] = 'vz_'.Str::random(40);

                    $tenant->update(['settings' => $settings]);

                    $this->form->fill([
                        'settings' => $settings,
                    ]);

                    Notification::make()
                        ->title('API Key Regenerated')
                        ->body('Your new API key is now active')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $tenant = auth()->user()->tenant;

            $tenant->update([
                'name' => $data['name'],
                'settings' => $data['settings'] ?? [],
            ]);

            Notification::make()
                ->title('Settings Saved')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
