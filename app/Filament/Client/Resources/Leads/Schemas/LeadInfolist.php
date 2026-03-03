<?php

namespace App\Filament\Client\Resources\Leads\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Basic Information Section
                Section::make('Basic Information')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Full Name')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('—'),

                        TextEntry::make('source')
                            ->badge()
                            ->color(fn (\App\Enums\LeadSource $state): string => match ($state) {
                                \App\Enums\LeadSource::Import => 'success',
                                \App\Enums\LeadSource::Manual => 'info',
                                \App\Enums\LeadSource::Api => 'warning',
                                \App\Enums\LeadSource::Form => 'primary',
                                \App\Enums\LeadSource::Whatsapp => 'success',
                                \App\Enums\LeadSource::Instagram => 'warning',
                                \App\Enums\LeadSource::FacebookMessenger => 'info',
                            })
                            ->formatStateUsing(fn (\App\Enums\LeadSource $state): string => __('leads.source.'.$state->value)),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->icon('heroicon-o-clock')
                            ->dateTime()
                            ->since(),
                    ]),

                // Contact Information Section
                Section::make('Contact Information')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('phones')
                            ->label('Phone Numbers')
                            ->icon('heroicon-o-phone')
                            ->badge()
                            ->separator(',')
                            ->formatStateUsing(fn ($state) => collect($state)->implode(', '))
                            ->copyable()
                            ->columnSpanFull()
                            ->hidden(fn ($record) => empty($record->phones)),

                        TextEntry::make('whatsapps')
                            ->label('WhatsApp Numbers')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->badge()
                            ->color('success')
                            ->separator(',')
                            ->formatStateUsing(fn ($state) => collect($state)->implode(', '))
                            ->copyable()
                            ->columnSpanFull()
                            ->hidden(fn ($record) => empty($record->whatsapps)),

                        TextEntry::make('primary_whatsapp')
                            ->label('Primary WhatsApp')
                            ->icon('heroicon-o-star')
                            ->badge()
                            ->color('success')
                            ->copyable()
                            ->columnSpanFull()
                            ->hidden(fn ($record) => empty($record->whatsapps)),
                    ]),

                // Address Section
                Section::make('Address')
                    ->icon('heroicon-o-map-pin')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('street_type')
                            ->placeholder('—'),

                        TextEntry::make('street_name')
                            ->columnSpan(2)
                            ->placeholder('—'),

                        TextEntry::make('number')
                            ->placeholder('—'),

                        TextEntry::make('complement')
                            ->columnSpan(2)
                            ->placeholder('—'),

                        TextEntry::make('district')
                            ->placeholder('—'),

                        TextEntry::make('neighborhood')
                            ->placeholder('—'),

                        TextEntry::make('region')
                            ->placeholder('—'),

                        TextEntry::make('city')
                            ->placeholder('—'),

                        TextEntry::make('postal_code')
                            ->label('Postal Code')
                            ->placeholder('—'),

                        TextEntry::make('country')
                            ->placeholder('—'),
                    ])
                    ->hidden(fn ($record) => empty($record->street_name) && empty($record->city)),

                // Lead Management Section
                Section::make('Lead Management')
                    ->icon('heroicon-o-briefcase')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('assignedUser.name')
                            ->label('Assigned To')
                            ->icon('heroicon-o-user-circle')
                            ->placeholder('Unassigned'),

                        TextEntry::make('pipelineStage.name')
                            ->label('Pipeline Stage')
                            ->icon('heroicon-o-queue-list')
                            ->badge()
                            ->color(fn ($record) => $record->pipelineStage?->color ?? 'gray')
                            ->placeholder('—'),

                        TextEntry::make('priority')
                            ->badge()
                            ->icon('heroicon-o-flag')
                            ->color(fn (string $state): string => match ($state) {
                                'low' => 'gray',
                                'medium' => 'info',
                                'high' => 'warning',
                                'urgent' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('tags')
                            ->badge()
                            ->separator(',')
                            ->color('primary')
                            ->placeholder('No tags')
                            ->columnSpanFull(),
                    ]),

                // Consent & Privacy Section
                Section::make('Consent & Privacy')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('consent_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'granted' => 'success',
                                'refused' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('consent_date')
                            ->date()
                            ->placeholder('—'),

                        IconEntry::make('opt_out')
                            ->label('Opted Out')
                            ->boolean()
                            ->trueIcon('heroicon-o-x-circle')
                            ->falseIcon('heroicon-o-check-circle')
                            ->trueColor('danger')
                            ->falseColor('success'),

                        IconEntry::make('do_not_contact')
                            ->label('Do Not Contact')
                            ->boolean()
                            ->trueIcon('heroicon-o-x-circle')
                            ->falseIcon('heroicon-o-check-circle')
                            ->trueColor('danger')
                            ->falseColor('success'),

                        TextEntry::make('opt_out_reason')
                            ->placeholder('—')
                            ->columnSpanFull()
                            ->hidden(fn ($record) => ! $record->opt_out),

                        TextEntry::make('opt_out_date')
                            ->date()
                            ->columnSpanFull()
                            ->hidden(fn ($record) => ! $record->opt_out),
                    ]),

                // Additional Information Section
                Section::make('Additional Information')
                    ->icon('heroicon-o-document-text')
                    ->columns(1)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('notes')
                            ->prose()
                            ->placeholder('No notes')
                            ->columnSpanFull(),

                        KeyValueEntry::make('custom_fields')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull()
                            ->hidden(fn ($record) => empty($record->custom_fields)),
                    ]),

                // Metadata Section
                Section::make('Metadata')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime(),

                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->badge()
                            ->color('danger')
                            ->columnSpanFull()
                            ->hidden(fn ($record) => ! $record->deleted_at),
                    ]),
            ]);
    }
}
