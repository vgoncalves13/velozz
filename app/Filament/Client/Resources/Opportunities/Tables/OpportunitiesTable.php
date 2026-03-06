<?php

namespace App\Filament\Client\Resources\Opportunities\Tables;

use Filament\Actions\Action;
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
                    ->label(__('opportunities.labels.lead'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->lead?->email),

                TextColumn::make('product.name')
                    ->label(__('opportunities.labels.product'))
                    ->searchable()
                    ->placeholder(__('opportunities.placeholders.not_available'))
                    ->toggleable(),

                TextColumn::make('value')
                    ->label(__('opportunities.labels.value'))
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('opportunityStage.name')
                    ->label(__('opportunities.labels.stage'))
                    ->badge()
                    ->color(fn ($record): string => $record->opportunityStage?->color ?? 'gray')
                    ->placeholder(__('opportunities.placeholders.not_set')),

                TextColumn::make('probability')
                    ->label(__('opportunities.labels.probability'))
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expected_close_date')
                    ->label(__('opportunities.labels.expected_close'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder(__('opportunities.placeholders.not_set'))
                    ->toggleable(),

                TextColumn::make('assignedUser.name')
                    ->label(__('opportunities.labels.assigned_to'))
                    ->searchable()
                    ->placeholder(__('opportunities.placeholders.unassigned'))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('opportunities.labels.created'))
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
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('opportunities.empty.title'))
            ->emptyStateDescription(__('opportunities.empty.description'))
            ->emptyStateIcon('heroicon-o-currency-euro')
            ->emptyStateActions([
                Action::make('create')
                    ->label(__('opportunities.actions.create'))
                    ->url(fn (): string => \App\Filament\Client\Resources\Opportunities\OpportunityResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
                Action::make('view_leads')
                    ->label(__('opportunities.actions.view_leads'))
                    ->url('/app/leads')
                    ->icon('heroicon-o-user-group')
                    ->color('gray'),
            ]);
    }
}
