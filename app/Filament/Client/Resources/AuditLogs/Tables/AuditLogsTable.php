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
                    ->label('Action')
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
                    ->label('Entity')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entity_id')
                    ->label('Entity ID')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->default('System')
                    ->weight(FontWeight::Medium),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('M d, Y H:i:s')),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'import' => 'Import',
                        'send_message' => 'Send Message',
                        'qr_code_access' => 'QR Code Access',
                        'lead_transfer' => 'Lead Transfer',
                        'gdpr_anonymization' => 'GDPR Anonymization',
                    ])
                    ->multiple(),

                SelectFilter::make('entity')
                    ->options([
                        'user' => 'User',
                        'lead' => 'Lead',
                        'import' => 'Import',
                        'whatsapp_message' => 'WhatsApp Message',
                        'whatsapp_instance' => 'WhatsApp Instance',
                    ])
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('User')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('system_actions')
                    ->label('System Actions')
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
