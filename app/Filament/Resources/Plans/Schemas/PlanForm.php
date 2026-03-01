<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plan Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Plan Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0),
                        TextInput::make('currency')
                            ->label('Currency')
                            ->required()
                            ->default('EUR')
                            ->maxLength(3),
                        TextInput::make('trial_days')
                            ->label('Trial Days')
                            ->required()
                            ->numeric()
                            ->default(30)
                            ->minValue(0),
                    ])->columns(2),

                Section::make('Limits')
                    ->schema([
                        TextInput::make('leads_limit_per_month')
                            ->label('Monthly Lead Limit')
                            ->required()
                            ->numeric()
                            ->default(1000)
                            ->minValue(1)
                            ->helperText('Maximum number of leads that can be created per month'),
                        TextInput::make('messages_limit_per_day')
                            ->label('Daily Message Limit')
                            ->required()
                            ->numeric()
                            ->default(500)
                            ->minValue(1)
                            ->helperText('Maximum number of messages that can be sent per day'),
                        TextInput::make('operators_limit')
                            ->label('Operators Limit')
                            ->required()
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->helperText('Maximum number of operators/users'),
                        TextInput::make('whatsapp_instances_limit')
                            ->label('WhatsApp Instances Limit')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('Maximum number of WhatsApp instances'),
                    ])->columns(2),
            ]);
    }
}
