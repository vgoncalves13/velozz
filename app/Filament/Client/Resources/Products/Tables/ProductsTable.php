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
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=Product&color=7F9CF5&background=EBF4FF'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->title),

                TextColumn::make('category')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('unit')
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
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
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Create your product catalog to start tracking opportunities and generating revenue.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create Product')
                    ->url(fn (): string => \App\Filament\Client\Resources\Products\ProductResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ]);
    }
}
