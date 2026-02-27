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
                Section::make('Audit Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('action')
                                    ->badge()
                                    ->color(fn (string $state): string => match (true) {
                                        str_contains($state, 'login') => 'success',
                                        str_contains($state, 'delete') || str_contains($state, 'gdpr') => 'danger',
                                        str_contains($state, 'update') || str_contains($state, 'edit') => 'warning',
                                        default => 'gray',
                                    }),

                                TextEntry::make('entity')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('entity_id')
                                    ->label('Entity ID'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('User')
                                    ->default('System'),

                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->since(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('ip_address')
                                    ->label('IP Address')
                                    ->copyable(),

                                TextEntry::make('user_agent')
                                    ->label('User Agent')
                                    ->limit(50)
                                    ->tooltip(fn ($record) => $record->user_agent),
                            ]),
                    ]),

                Section::make('Previous Data')
                    ->schema([
                        KeyValueEntry::make('previous_data')
                            ->label('')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),
                    ])
                    ->visible(fn ($record) => ! empty($record->previous_data))
                    ->collapsed(),

                Section::make('New Data')
                    ->schema([
                        KeyValueEntry::make('new_data')
                            ->label('')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),
                    ])
                    ->visible(fn ($record) => ! empty($record->new_data))
                    ->collapsed(),
            ]);
    }
}
