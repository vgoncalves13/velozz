<?php

namespace App\Filament\Client\Resources\LeadWidgets\Tables;

use App\Filament\Client\Resources\LeadWidgets\EmbeddedFormResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmbeddedFormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('lead_widgets.labels.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('lead_widgets.labels.slug'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('fields')
                    ->label(__('lead_widgets.labels.fields_count'))
                    ->formatStateUsing(fn ($state) => count((array) $state).' '.__('lead_widgets.labels.fields'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label(__('lead_widgets.labels.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('lead_widgets.status.active'),
                        'inactive' => __('lead_widgets.status.inactive'),
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label(__('lead_widgets.labels.created'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('lead_widgets.embedded_forms.empty.title'))
            ->emptyStateDescription(__('lead_widgets.embedded_forms.empty.description'))
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Action::make('create')
                    ->label(__('lead_widgets.embedded_forms.actions.create'))
                    ->url(fn (): string => EmbeddedFormResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ]);
    }
}
