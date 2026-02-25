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
            ->columns(2)
            ->components([
                // Basic Information Section
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('full_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Select::make('source')
                            ->options([
                                'import' => 'Import',
                                'manual' => 'Manual',
                                'api' => 'API',
                                'form' => 'Form',
                            ])
                            ->default('manual')
                            ->required(),
                    ]),

                // Phone Numbers Section
                Section::make('Contact Information')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Repeater::make('phones')
                            ->label('Phone Numbers')
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
                    ->columns(3)
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
                    ->columns(2)
                    ->schema([
                        Select::make('assigned_user_id')
                            ->label('Assigned To')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('pipeline_stage_id')
                            ->label('Pipeline Stage')
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
                            ->default('medium')
                            ->required(),

                        TagsInput::make('tags')
                            ->separator(',')
                            ->placeholder('Add tags...')
                            ->columnSpanFull(),
                    ]),

                // Consent & Privacy Section
                Section::make('Consent & Privacy')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Select::make('consent_status')
                            ->options([
                                'pending' => 'Pending',
                                'granted' => 'Granted',
                                'refused' => 'Refused',
                            ])
                            ->default('pending')
                            ->required(),

                        DatePicker::make('consent_date'),

                        Toggle::make('opt_out')
                            ->label('Opted Out'),

                        Toggle::make('do_not_contact')
                            ->label('Do Not Contact'),

                        TextInput::make('opt_out_reason')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        DatePicker::make('opt_out_date')
                            ->columnSpanFull(),
                    ]),

                // Notes & Custom Fields Section
                Section::make('Additional Information')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),

                        KeyValue::make('custom_fields')
                            ->keyLabel('Field Name')
                            ->valueLabel('Value')
                            ->addButtonLabel('Add Custom Field')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
