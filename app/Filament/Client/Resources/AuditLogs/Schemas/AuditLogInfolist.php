<?php

namespace App\Filament\Client\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('audit_logs.sections.details'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('action')
                                    ->label(__('audit_logs.columns.action'))
                                    ->badge()
                                    ->color(fn (string $state): string => match (true) {
                                        str_contains($state, 'login') => 'success',
                                        str_contains($state, 'delete') || str_contains($state, 'gdpr') => 'danger',
                                        str_contains($state, 'update') || str_contains($state, 'edit') => 'warning',
                                        default => 'gray',
                                    }),

                                TextEntry::make('entity')
                                    ->label(__('audit_logs.columns.entity'))
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('entity_id')
                                    ->label(__('audit_logs.columns.entity_id')),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label(__('audit_logs.columns.user'))
                                    ->default(__('audit_logs.defaults.system')),

                                TextEntry::make('created_at')
                                    ->label(__('audit_logs.columns.date'))
                                    ->dateTime()
                                    ->since(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('ip_address')
                                    ->label(__('audit_logs.columns.ip_address'))
                                    ->copyable(),

                                TextEntry::make('user_agent')
                                    ->label(__('audit_logs.columns.user_agent'))
                                    ->limit(50)
                                    ->tooltip(fn ($record) => $record->user_agent),
                            ]),
                    ]),

                Section::make(__('audit_logs.sections.previous_data'))
                    ->schema([
                        KeyValueEntry::make('previous_data')
                            ->label('')
                            ->keyLabel(__('audit_logs.fields.field'))
                            ->valueLabel(__('audit_logs.fields.value')),
                    ])
                    ->visible(fn ($record) => ! empty($record->previous_data))
                    ->collapsed(),

                Section::make(__('audit_logs.sections.new_data'))
                    ->schema([
                        KeyValueEntry::make('new_data')
                            ->label('')
                            ->keyLabel(__('audit_logs.fields.field'))
                            ->valueLabel(__('audit_logs.fields.value')),
                    ])
                    ->visible(fn ($record) => ! empty($record->new_data))
                    ->collapsed(),
            ]);
    }
}
