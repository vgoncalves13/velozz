<?php

namespace App\Filament\Client\Resources\Opportunities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('opportunities.sections.opportunity_information'))
                    ->icon('heroicon-o-light-bulb')
                    ->schema([
                        Select::make('lead_id')
                            ->label(__('opportunities.labels.lead'))
                            ->relationship('lead', 'full_name')
                            ->searchable()
                            ->required()
                            ->helperText(__('opportunities.helper.lead')),

                        Select::make('product_id')
                            ->label(__('opportunities.labels.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->helperText(__('opportunities.helper.product')),

                        Select::make('assigned_user_id')
                            ->label(__('opportunities.labels.assigned_to'))
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->helperText(__('opportunities.helper.assigned_to')),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make(__('opportunities.sections.value_and_stage'))
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        TextInput::make('value')
                            ->label(__('opportunities.labels.value'))
                            ->helperText(__('opportunities.helper.value'))
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),

                        Select::make('opportunity_stage_id')
                            ->label(__('opportunities.labels.stage'))
                            ->helperText(__('opportunities.helper.stage'))
                            ->relationship('opportunityStage', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('probability')
                            ->label(__('opportunities.labels.probability_percent'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(25)
                            ->helperText(__('opportunities.helper.probability')),

                        DatePicker::make('expected_close_date')
                            ->label(__('opportunities.labels.expected_close_date'))
                            ->helperText(__('opportunities.helper.expected_close_date')),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make(__('opportunities.sections.additional_information'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('opportunities.labels.notes'))
                            ->rows(3)
                            ->helperText(__('opportunities.helper.notes')),

                        Textarea::make('loss_reason')
                            ->label(__('opportunities.labels.loss_reason'))
                            ->rows(2)
                            ->helperText(__('opportunities.helper.loss_reason')),
                    ])
                    ->collapsible(),
            ]);
    }
}
