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
                Section::make(__('leads.sections.basic_information'))
                    ->icon('heroicon-o-user')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('full_name')
                            ->label(__('fields.full_name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label(__('fields.email'))
                            ->email()
                            ->maxLength(255)
                            ->helperText(__('leads.helper.email')),

                        Select::make('source')
                            ->label(__('fields.source'))
                            ->options(fn () => collect(\App\Enums\LeadSource::cases())
                                ->mapWithKeys(fn (\App\Enums\LeadSource $case) => [
                                    $case->value => __('leads.source.'.$case->value),
                                ])
                                ->all())
                            ->default('manual')
                            ->helperText(__('leads.helper.source'))
                            ->required(),
                    ]),

                // Phone Numbers Section
                Section::make(__('leads.sections.contact_information'))
                    ->icon('heroicon-o-phone')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Repeater::make('phones')
                            ->label(__('fields.phone_numbers'))
                            ->helperText(__('leads.helper.phones'))
                            ->simple(
                                TextInput::make('phone')
                                    ->tel()
                                    ->placeholder(__('leads.placeholders.phone'))
                                    ->maxLength(255)
                            )
                            ->defaultItems(0)
                            ->addActionLabel(__('leads.actions.add_phone_number'))
                            ->collapsible()
                            ->itemLabel(fn ($state): ?string => $state ?? null)
                            ->columnSpanFull(),

                        Repeater::make('whatsapps')
                            ->label(__('fields.whatsapp_numbers'))
                            ->helperText(__('leads.helper.whatsapps'))
                            ->simple(
                                TextInput::make('whatsapp')
                                    ->tel()
                                    ->placeholder(__('leads.placeholders.phone'))
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                            )
                            ->defaultItems(0)
                            ->addActionLabel(__('leads.actions.add_whatsapp_number'))
                            ->collapsible()
                            ->itemLabel(fn ($state): ?string => $state ?? null)
                            ->live()
                            ->columnSpanFull(),

                        Select::make('primary_whatsapp_index')
                            ->label(__('fields.primary_whatsapp'))
                            ->helperText(__('leads.helper.primary_whatsapp'))
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
                                    $options[$index] = __('leads.labels.whatsapp_number').' '.($index + 1).": {$number}";
                                }

                                return $options;
                            })
                            ->default(0)
                            ->visible(fn ($get) => ! empty($get('whatsapps')))
                            ->columnSpanFull(),
                    ]),

                // Address Section
                Section::make(__('leads.sections.address'))
                    ->icon('heroicon-o-map-pin')
                    ->columns(['default' => 1, 'md' => 2, 'lg' => 3])
                    ->collapsible()
                    ->schema([
                        TextInput::make('street_type')
                            ->label(__('fields.street_type'))
                            ->maxLength(255),
                        TextInput::make('street_name')
                            ->label(__('fields.street_name'))
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('number')
                            ->label(__('fields.number'))
                            ->maxLength(255),
                        TextInput::make('complement')
                            ->label(__('fields.complement'))
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('district')
                            ->label(__('fields.district'))
                            ->maxLength(255),
                        TextInput::make('neighborhood')
                            ->label(__('fields.neighborhood'))
                            ->maxLength(255),
                        TextInput::make('region')
                            ->label(__('fields.region'))
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label(__('fields.city'))
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label(__('fields.postal_code'))
                            ->maxLength(255),
                        TextInput::make('country')
                            ->label(__('fields.country'))
                            ->default('Portugal')
                            ->maxLength(255),
                    ]),

                // Lead Management Section
                Section::make(__('leads.sections.lead_management'))
                    ->icon('heroicon-o-briefcase')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        Select::make('assigned_user_id')
                            ->label(__('fields.assigned_to'))
                            ->helperText(__('leads.helper.assigned_to'))
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('pipeline_stage_id')
                            ->label(__('fields.pipeline_stage'))
                            ->helperText(__('leads.helper.pipeline_stage'))
                            ->relationship('pipelineStage', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('priority')
                            ->label(__('fields.priority'))
                            ->options([
                                'low' => __('leads.priority.low'),
                                'medium' => __('leads.priority.medium'),
                                'high' => __('leads.priority.high'),
                                'urgent' => __('leads.priority.urgent'),
                            ])
                            ->helperText(__('leads.helper.priority'))
                            ->default('medium')
                            ->required(),

                        TagsInput::make('tags')
                            ->label(__('fields.tags'))
                            ->separator(',')
                            ->helperText(__('leads.helper.tags'))
                            ->placeholder(__('leads.placeholders.tags'))
                            ->columnSpanFull(),
                    ]),

                // Consent & Privacy Section
                Section::make(__('leads.sections.consent_privacy'))
                    ->icon('heroicon-o-shield-check')
                    ->columns(['default' => 1, 'md' => 2])
                    ->collapsible()
                    ->schema([
                        Select::make('consent_status')
                            ->label(__('fields.consent_status'))
                            ->options([
                                'pending' => __('leads.consent_status.pending'),
                                'granted' => __('leads.consent_status.granted'),
                                'refused' => __('leads.consent_status.refused'),
                            ])
                            ->helperText(__('leads.helper.consent_status'))
                            ->default('pending')
                            ->required(),

                        DatePicker::make('consent_date')
                            ->label(__('fields.consent_date'))
                            ->helperText(__('leads.helper.consent_date')),

                        Toggle::make('opt_out')
                            ->label(__('fields.opt_out'))
                            ->helperText(__('leads.helper.opt_out')),

                        Toggle::make('do_not_contact')
                            ->label(__('fields.do_not_contact'))
                            ->helperText(__('leads.helper.do_not_contact')),

                        TextInput::make('opt_out_reason')
                            ->label(__('fields.opt_out_reason'))
                            ->helperText(__('leads.helper.opt_out_reason'))
                            ->maxLength(255)
                            ->columnSpanFull(),

                        DatePicker::make('opt_out_date')
                            ->label(__('fields.opt_out_date'))
                            ->helperText(__('leads.helper.opt_out_date'))
                            ->columnSpanFull(),
                    ]),

                // Notes & Custom Fields Section
                Section::make(__('leads.sections.additional_information'))
                    ->icon('heroicon-o-document-text')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('fields.notes'))
                            ->helperText(__('leads.helper.notes'))
                            ->rows(4)
                            ->columnSpanFull(),

                        KeyValue::make('custom_fields')
                            ->label(__('fields.custom_fields'))
                            ->keyLabel(__('leads.labels.key_label'))
                            ->valueLabel(__('leads.labels.value_label'))
                            ->helperText(__('leads.helper.custom_fields'))
                            ->addButtonLabel(__('leads.actions.add_custom_field'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
