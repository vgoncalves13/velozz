<?php

namespace App\Filament\Resources\Plans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Plan Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('trial_days')
                    ->label('Trial Days')
                    ->numeric()
                    ->sortable()
                    ->suffix(' days'),
                TextColumn::make('tenants_count')
                    ->label('Active Tenants')
                    ->counts('tenants')
                    ->sortable(),
                TextColumn::make('leads_limit_per_month')
                    ->label('Leads/Month')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('messages_limit_per_day')
                    ->label('Messages/Day')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('operators_limit')
                    ->label('Operators')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('whatsapp_instances_limit')
                    ->label('WhatsApp Instances')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
