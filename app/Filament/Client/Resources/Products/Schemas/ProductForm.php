<?php

namespace App\Filament\Client\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('products.sections.basic_information'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('products.labels.name'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('products.helper.name')),

                        TextInput::make('title')
                            ->label(__('products.labels.title'))
                            ->maxLength(255)
                            ->helperText(__('products.helper.title')),

                        TextInput::make('category')
                            ->label(__('products.labels.category'))
                            ->helperText(__('products.helper.category'))
                            ->maxLength(255)
                            ->datalist([
                                __('products.categories.service'),
                                __('products.categories.product'),
                                __('products.categories.subscription'),
                                __('products.categories.consultation'),
                            ]),

                        Textarea::make('description')
                            ->label(__('products.labels.description'))
                            ->helperText(__('products.helper.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make(__('products.sections.pricing'))
                    ->icon('heroicon-o-currency-euro')
                    ->schema([
                        TextInput::make('price')
                            ->label(__('products.labels.price'))
                            ->helperText(__('products.helper.price'))
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),

                        Select::make('currency')
                            ->label(__('products.labels.currency'))
                            ->helperText(__('products.helper.currency'))
                            ->options([
                                'EUR' => __('products.currencies.eur'),
                                'USD' => __('products.currencies.usd'),
                                'GBP' => __('products.currencies.gbp'),
                            ])
                            ->default('EUR')
                            ->required(),

                        TextInput::make('unit')
                            ->label(__('products.labels.unit'))
                            ->maxLength(255)
                            ->placeholder(__('products.placeholders.unit'))
                            ->helperText(__('products.helper.unit')),

                        Select::make('status')
                            ->label(__('products.labels.status'))
                            ->helperText(__('products.helper.status'))
                            ->options([
                                'active' => __('products.status.active'),
                                'inactive' => __('products.status.inactive'),
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make(__('products.sections.image'))
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('image_url')
                            ->label(__('products.labels.product_image'))
                            ->helperText(__('products.helper.image'))
                            ->image()
                            ->imageEditor()
                            ->directory('products')
                            ->visibility('public')
                            ->maxSize(2048),
                    ])
                    ->collapsible(),
            ]);
    }
}
