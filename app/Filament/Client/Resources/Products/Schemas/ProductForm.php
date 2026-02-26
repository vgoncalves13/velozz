<?php

namespace App\Filament\Client\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Internal product name for reference'),

                        TextInput::make('title')
                            ->maxLength(255)
                            ->helperText('Display title (leave empty to use name)'),

                        TextInput::make('category')
                            ->maxLength(255)
                            ->datalist(['Service', 'Product', 'Subscription', 'Consultation']),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),

                        Select::make('currency')
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
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Image')
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('Product Image')
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
