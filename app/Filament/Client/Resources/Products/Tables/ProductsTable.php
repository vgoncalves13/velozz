<?php

namespace App\Filament\Client\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label(__('products.labels.image'))
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=Product&color=7F9CF5&background=EBF4FF'),

                TextColumn::make('name')
                    ->label(__('products.labels.name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->title),

                TextColumn::make('category')
                    ->label(__('products.labels.category'))
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label(__('products.labels.price'))
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('unit')
                    ->label(__('products.labels.unit'))
                    ->placeholder(__('products.placeholders.not_available'))
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('products.labels.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('products.status.active'),
                        'inactive' => __('products.status.inactive'),
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label(__('products.labels.created'))
                    ->dateTime('d/m/Y')
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
            ->emptyStateHeading(__('products.empty.title'))
            ->emptyStateDescription(__('products.empty.description'))
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->emptyStateActions([
                Action::make('create')
                    ->label(__('products.actions.create'))
                    ->url(fn (): string => \App\Filament\Client\Resources\Products\ProductResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ]);
    }
}
