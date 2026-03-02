<?php

namespace App\Filament\Client\Resources\PipelineStages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PipelineStagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label(__('pipeline_stages.labels.order'))
                    ->sortable()
                    ->toggleable()
                    ->width(80),

                ColorColumn::make('color')
                    ->label(__('pipeline_stages.labels.color'))
                    ->width(60)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label(__('pipeline_stages.labels.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                IconColumn::make('icon')
                    ->label(__('pipeline_stages.labels.icon'))
                    ->icon(fn ($record) => $record->icon ?? 'heroicon-o-queue-list')
                    ->toggleable(),

                TextColumn::make('sla_hours')
                    ->label(__('pipeline_stages.labels.sla'))
                    ->suffix(' '.__('pipeline_stages.labels.hours'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('leads_count')
                    ->label(__('pipeline_stages.labels.leads_count'))
                    ->counts('leads')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('pipeline_stages.labels.created_at'))
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
