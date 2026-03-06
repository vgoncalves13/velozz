<?php

namespace App\Filament\Client\Resources\OpportunityStages\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OpportunityStageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'md' => 2])
            ->components([
                Section::make(__('opportunity_stages.sections.basic_information'))
                    ->icon('heroicon-o-information-circle')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->label(__('opportunity_stages.labels.name'))
                            ->helperText(__('opportunity_stages.helper.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        ColorPicker::make('color')
                            ->label(__('opportunity_stages.labels.color'))
                            ->helperText(__('opportunity_stages.helper.color'))
                            ->required()
                            ->default('#3b82f6'),

                        TextInput::make('order')
                            ->label(__('opportunity_stages.labels.order'))
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->helperText(__('opportunity_stages.helper.order')),

                        Select::make('icon')
                            ->label(__('opportunity_stages.labels.icon'))
                            ->helperText(__('opportunity_stages.helper.icon'))
                            ->options([
                                'heroicon-o-inbox' => __('opportunity_stages.icons.inbox'),
                                'heroicon-o-phone' => __('opportunity_stages.icons.phone'),
                                'heroicon-o-chat-bubble-left-right' => __('opportunity_stages.icons.chat'),
                                'heroicon-o-currency-dollar' => __('opportunity_stages.icons.currency_dollar'),
                                'heroicon-o-check-circle' => __('opportunity_stages.icons.check_circle'),
                                'heroicon-o-x-circle' => __('opportunity_stages.icons.x_circle'),
                                'heroicon-o-clock' => __('opportunity_stages.icons.clock'),
                                'heroicon-o-star' => __('opportunity_stages.icons.star'),
                                'heroicon-o-flag' => __('opportunity_stages.icons.flag'),
                                'heroicon-o-document-text' => __('opportunity_stages.icons.document'),
                                'heroicon-o-hand-raised' => __('opportunity_stages.icons.handshake'),
                            ])
                            ->default('heroicon-o-currency-dollar')
                            ->columnSpanFull(),

                        TextInput::make('sla_hours')
                            ->label(__('opportunity_stages.labels.sla_hours'))
                            ->numeric()
                            ->nullable()
                            ->minValue(0)
                            ->helperText(__('opportunity_stages.helper.sla_hours'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
