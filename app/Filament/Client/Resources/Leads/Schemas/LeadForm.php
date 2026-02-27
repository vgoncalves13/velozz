<?php

namespace App\Filament\Client\Resources\Leads\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'sm' => 1, 'md' => 2])
            ->components([
                // Basic Information Section
                Section::make('Basic Information')
                    ->icon('heroicon-o-user')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('full_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Email address for notifications and communication'),

                        Select::make('source')
                            ->options([
                                'import' => 'Import',
                                'manual' => 'Manual',
                                'api' => 'API',
                                'form' => 'Form',
                            ])
                            ->default('manual')
                            ->helperText('How this lead was acquired')
                            ->required(),
                    ]),

                // Phone Numbers Section
                Section::make('Contact Information')
                    ->icon('heroicon-o-phone')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Repeater::make('phones')
                            ->label('Phone Numbers')
                            ->helperText('Regular phone numbers for voice calls')
                            ->simple(
                                TextInput::make('phone')
                                    ->tel()
                                    ->placeholder('+351 912 345 678')
                                    ->maxLength(255)
                            )
                            ->defaultItems(0)
                            ->addActionLabel('Add Phone Number')
                            ->collapsible()
                            ->itemLabel(fn ($state): ?string => $state ?? null)
                            ->columnSpanFull(),

                        Repeater::make('whatsapps')
                            ->label('WhatsApp Numbers')
                            ->helperText('WhatsApp numbers for messaging (must include country code)')
                            ->simple(
                                TextInput::make('whatsapp')
                                    ->tel()
                                    ->placeholder('+351 912 345 678')
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                            )
                            ->defaultItems(0)
                            ->addActionLabel('Add WhatsApp Number')
                            ->collapsible()
                            ->itemLabel(fn ($state): ?string => $state ?? null)
                            ->live()
                            ->columnSpanFull(),

                        Select::make('primary_whatsapp_index')
                            ->label('Primary WhatsApp')
                            ->helperText('Select which WhatsApp number is the primary contact')
                            ->options(function ($get) {
                                $whatsapps = $get('whatsapps');

                                if (empty($whatsapps) || ! is_array($whatsapps)) {
                                    return [];
                                }

                                // Reindex array numerically (Repeater uses UUIDs as keys)
                                $whatsapps = array_values($whatsapps);

                                $options = [];
                                foreach ($whatsapps as $index => $wa) {
                                    $number = is_string($wa) ? $wa : ($wa['whatsapp'] ?? 'Empty');
                                    $options[$index] = 'WhatsApp '.($index + 1).": {$number}";
                                }

                                return $options;
                            })
                            ->default(0)
                            ->visible(fn ($get) => ! empty($get('whatsapps')))
                            ->columnSpanFull(),
                    ]),

                // Address Section
                Section::make('Address')
                    ->icon('heroicon-o-map-pin')
                    ->columns(['default' => 1, 'md' => 2, 'lg' => 3])
                    ->collapsible()
                    ->schema([
                        TextInput::make('street_type')
                            ->maxLength(255),
                        TextInput::make('street_name')
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('number')
                            ->maxLength(255),
                        TextInput::make('complement')
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('district')
                            ->maxLength(255),
                        TextInput::make('neighborhood')
                            ->maxLength(255),
                        TextInput::make('region')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->maxLength(255),
                        TextInput::make('country')
                            ->default('Portugal')
                            ->maxLength(255),
                    ]),

                // Lead Management Section
                Section::make('Lead Management')
                    ->icon('heroicon-o-briefcase')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        Select::make('assigned_user_id')
                            ->label('Assigned To')
                            ->helperText('Team member responsible for this lead')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('pipeline_stage_id')
                            ->label('Pipeline Stage')
                            ->helperText('Current stage in your sales pipeline')
                            ->relationship('pipelineStage', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->helperText('Urgent: immediate action required | High: contact within 24h | Medium: contact within week | Low: standard follow-up')
                            ->default('medium')
                            ->required(),

                        TagsInput::make('tags')
                            ->separator(',')
                            ->helperText('Add keywords for easy filtering (e.g., vip, hot-lead, follow-up)')
                            ->placeholder('Add tags...')
                            ->columnSpanFull(),
                    ]),

                // Consent & Privacy Section
                Section::make('Consent & Privacy')
                    ->icon('heroicon-o-shield-check')
                    ->columns(['default' => 1, 'md' => 2])
                    ->collapsible()
                    ->schema([
                        Select::make('consent_status')
                            ->options([
                                'pending' => 'Pending',
                                'granted' => 'Granted',
                                'refused' => 'Refused',
                            ])
                            ->helperText('GDPR consent status for communication')
                            ->default('pending')
                            ->required(),

                        DatePicker::make('consent_date')
                            ->helperText('Date when consent was given or refused'),

                        Toggle::make('opt_out')
                            ->label('Opted Out')
                            ->helperText('Lead requested to stop receiving communications'),

                        Toggle::make('do_not_contact')
                            ->label('Do Not Contact')
                            ->helperText('Internal flag to prevent all contact (e.g., legal restriction)'),

                        TextInput::make('opt_out_reason')
                            ->helperText('Reason for opting out')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        DatePicker::make('opt_out_date')
                            ->helperText('Date when lead opted out')
                            ->columnSpanFull(),
                    ]),

                // Notes & Custom Fields Section
                Section::make('Additional Information')
                    ->icon('heroicon-o-document-text')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Textarea::make('notes')
                            ->helperText('Internal notes about this lead (not visible to the lead)')
                            ->rows(4)
                            ->columnSpanFull(),

                        KeyValue::make('custom_fields')
                            ->keyLabel('Field Name')
                            ->valueLabel('Value')
                            ->helperText('Add any additional information specific to your business needs')
                            ->addButtonLabel('Add Custom Field')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
