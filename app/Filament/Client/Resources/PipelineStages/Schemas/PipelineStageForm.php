<?php

namespace App\Filament\Client\Resources\PipelineStages\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PipelineStageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Basic Information')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        ColorPicker::make('color')
                            ->required()
                            ->default('#3b82f6'),

                        TextInput::make('order')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Order in which this stage appears in the pipeline'),

                        Select::make('icon')
                            ->options([
                                'heroicon-o-inbox' => 'Inbox',
                                'heroicon-o-phone' => 'Phone',
                                'heroicon-o-chat-bubble-left-right' => 'Chat',
                                'heroicon-o-currency-dollar' => 'Dollar',
                                'heroicon-o-check-circle' => 'Check Circle',
                                'heroicon-o-x-circle' => 'X Circle',
                                'heroicon-o-clock' => 'Clock',
                                'heroicon-o-star' => 'Star',
                                'heroicon-o-flag' => 'Flag',
                            ])
                            ->default('heroicon-o-queue-list')
                            ->columnSpanFull(),

                        TextInput::make('sla_hours')
                            ->label('SLA (hours)')
                            ->numeric()
                            ->nullable()
                            ->minValue(0)
                            ->helperText('Expected time for leads to stay in this stage (optional)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Automations')
                    ->icon('heroicon-o-bolt')
                    ->columns(1)
                    ->collapsible()
                    ->collapsed()
                    ->description('Configure automations that trigger when a lead enters or exits this stage')
                    ->schema([
                        TextInput::make('automacao_entrada.template_id')
                            ->label('Entry Automation - Template ID')
                            ->helperText('WhatsApp template to send when lead enters this stage')
                            ->placeholder('e.g., welcome_template'),

                        TextInput::make('automacao_entrada.operador_id')
                            ->label('Entry Automation - Auto-assign Operator ID')
                            ->helperText('Automatically assign lead to this operator')
                            ->placeholder('e.g., 5'),

                        TextInput::make('automacao_entrada.tags')
                            ->label('Entry Automation - Add Tags')
                            ->helperText('Comma-separated tags to add')
                            ->placeholder('e.g., contacted,priority'),

                        TextInput::make('automacao_saida.webhook_url')
                            ->label('Exit Automation - Webhook URL')
                            ->url()
                            ->helperText('URL to call when lead exits this stage')
                            ->placeholder('https://example.com/webhook'),
                    ]),
            ]);
    }
}
