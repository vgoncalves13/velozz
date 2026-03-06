<?php

namespace App\Filament\Client\Resources\OpportunityStages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OpportunityStagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label(__('opportunity_stages.labels.order'))
                    ->sortable()
                    ->toggleable()
                    ->width(80),

                ColorColumn::make('color')
                    ->label(__('opportunity_stages.labels.color'))
                    ->width(60)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label(__('opportunity_stages.labels.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                IconColumn::make('icon')
                    ->label(__('opportunity_stages.labels.icon'))
                    ->icon(fn ($record) => $record->icon ?? 'heroicon-o-currency-dollar')
                    ->toggleable(),

                TextColumn::make('sla_hours')
                    ->label(__('opportunity_stages.labels.sla'))
                    ->suffix(' '.__('opportunity_stages.labels.hours'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('opportunities_count')
                    ->label(__('opportunity_stages.labels.opportunities_count'))
                    ->counts('opportunities')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('opportunity_stages.labels.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
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
            ->reorderable('order')
            ->defaultSort('order', 'asc');
    }
}
