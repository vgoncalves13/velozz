<?php

namespace App\Filament\Client\Resources\WhatsAppTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsAppTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),

                TextColumn::make('content')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->content)
                    ->label('Content'),

                IconColumn::make('active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('trigger_on')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'gray',
                        'lead_created' => 'success',
                        'import' => 'info',
                        'stage' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manual' => 'Manual',
                        'lead_created' => 'On Create',
                        'import' => 'On Import',
                        'stage' => 'Stage Trigger',
                        default => $state,
                    })
                    ->label('Trigger'),

                TextColumn::make('pipelineStage.name')
                    ->label('Stage')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created'),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),

                SelectFilter::make('trigger_on')
                    ->options([
                        'manual' => 'Manual',
                        'lead_created' => 'On Create',
                        'import' => 'On Import',
                        'stage' => 'Stage Trigger',
                    ])
                    ->label('Trigger Type'),
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
