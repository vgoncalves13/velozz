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
                Section::make('Basic Information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Internal product name for reference'),

                        TextInput::make('title')
                            ->maxLength(255)
                            ->helperText('Display title (leave empty to use name)'),

                        TextInput::make('category')
                            ->helperText('Product category for organization and filtering')
                            ->maxLength(255)
                            ->datalist(['Service', 'Product', 'Subscription', 'Consultation']),

                        Textarea::make('description')
                            ->helperText('Detailed product description visible to your team')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make('Pricing')
                    ->icon('heroicon-o-currency-euro')
                    ->schema([
                        TextInput::make('price')
                            ->helperText('Base price per unit')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),

                        Select::make('currency')
                            ->helperText('Currency for pricing')
                            ->options([
                                'EUR' => 'EUR (€)',
                                'USD' => 'USD ($)',
                                'GBP' => 'GBP (£)',
                            ])
                            ->default('EUR')
                            ->required(),

                        TextInput::make('unit')
                            ->maxLength(255)
                            ->placeholder('piece, hour, kg, etc.')
                            ->helperText('Unit of measurement'),

                        Select::make('status')
                            ->helperText('Active products are available for creating opportunities')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make('Image')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('Product Image')
                            ->helperText('Optional product image (max 2MB)')
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
