<?php

namespace App\Filament\Client\Resources\Opportunities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.full_name')
                    ->label('Lead')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->lead?->email),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('value')
                    ->label('Value')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('stage')
                    ->label('Stage')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'proposal' => 'info',
                        'negotiation' => 'warning',
                        'closed_won' => 'success',
                        'closed_lost' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'proposal' => 'Proposal',
                        'negotiation' => 'Negotiation',
                        'closed_won' => 'Closed Won',
                        'closed_lost' => 'Closed Lost',
                        default => $state,
                    }),

                TextColumn::make('probability')
                    ->label('Probability')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expected_close_date')
                    ->label('Expected Close')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Not set')
                    ->toggleable(),

                TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->placeholder('Unassigned')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
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
            ->defaultSort('created_at', 'desc');
    }
}
