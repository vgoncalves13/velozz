<?php

namespace App\Filament\Client\Resources\Opportunities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Opportunity Information')
                    ->schema([
                        Select::make('lead_id')
                            ->label('Lead')
                            ->relationship('lead', 'full_name')
                            ->searchable()
                            ->required()
                            ->helperText('Select the lead for this opportunity'),

                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->helperText('Optional: Select a product for this opportunity'),

                        Select::make('assigned_user_id')
                            ->label('Assigned To')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->helperText('Assign an operator to this opportunity'),
                    ])
                    ->columns(2),

                Section::make('Value & Stage')
                    ->schema([
                        TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),

                        Select::make('stage')
                            ->label('Stage')
                            ->options([
                                'proposal' => 'Proposal',
                                'negotiation' => 'Negotiation',
                                'closed_won' => 'Closed Won',
                                'closed_lost' => 'Closed Lost',
                            ])
                            ->default('proposal')
                            ->required(),

                        TextInput::make('probability')
                            ->label('Probability (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(25)
                            ->helperText('Chance of closing (0-100%)'),

                        DatePicker::make('expected_close_date')
                            ->label('Expected Close Date')
                            ->helperText('When do you expect to close this deal?'),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->helperText('Internal notes about this opportunity'),

                        Textarea::make('loss_reason')
                            ->label('Loss Reason')
                            ->rows(2)
                            ->helperText('If lost, why? (Optional)')
                            ->visible(fn ($get) => $get('stage') === 'closed_lost'),
                    ])
                    ->collapsible(),
            ]);
    }
}
