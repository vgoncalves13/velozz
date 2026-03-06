<?php

namespace App\Filament\Client\Resources\AuditLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('action')
                    ->label(__('audit_logs.columns.action'))
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'login') => 'success',
                        str_contains($state, 'delete') || str_contains($state, 'gdpr') => 'danger',
                        str_contains($state, 'update') || str_contains($state, 'edit') => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('entity')
                    ->label(__('audit_logs.columns.entity'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entity_id')
                    ->label(__('audit_logs.columns.entity_id'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label(__('audit_logs.columns.user'))
                    ->searchable()
                    ->sortable()
                    ->default(__('audit_logs.defaults.system'))
                    ->weight(FontWeight::Medium),

                TextColumn::make('ip_address')
                    ->label(__('audit_logs.columns.ip_address'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('audit_logs.columns.date'))
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('M d, Y H:i:s')),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label(__('audit_logs.filters.action'))
                    ->options(__('audit_logs.filters.actions'))
                    ->multiple(),

                SelectFilter::make('entity')
                    ->label(__('audit_logs.filters.entity'))
                    ->options(__('audit_logs.filters.entities'))
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label(__('audit_logs.filters.user'))
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('system_actions')
                    ->label(__('audit_logs.filters.system_actions'))
                    ->nullable()
                    ->queries(
                        true: fn ($query) => $query->whereNull('user_id'),
                        false: fn ($query) => $query->whereNotNull('user_id'),
                    ),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->poll('30s');
    }
}
