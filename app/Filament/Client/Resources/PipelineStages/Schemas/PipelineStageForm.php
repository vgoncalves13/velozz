<?php

namespace App\Filament\Client\Resources\PipelineStages\Schemas;

use App\Models\User;
use App\Models\WhatsAppTemplate;
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
            ->columns(['default' => 1, 'md' => 2])
            ->components([
                Section::make('Basic Information')
                    ->icon('heroicon-o-information-circle')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->helperText('Stage name (e.g., "New Lead", "Contacted", "Proposal Sent", "Closed Won")')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        ColorPicker::make('color')
                            ->helperText('Color used to identify this stage in the Kanban board')
                            ->required()
                            ->default('#3b82f6'),

                        TextInput::make('order')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Order in which this stage appears in the pipeline'),

                        Select::make('icon')
                            ->helperText('Icon displayed in the Kanban board header')
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
                        Select::make('automacao_entrada.template_id')
                            ->label('Entry Automation - WhatsApp Template')
                            ->helperText('Automatically send this WhatsApp template when lead enters this stage')
                            ->options(fn () => WhatsAppTemplate::where('tenant_id', auth()->user()->tenant_id)
                                ->where('active', true)
                                ->pluck('name', 'id')
                                ->take(100))
                            ->searchable()
                            ->preload()
                            ->allowHtml()
                            ->getSearchResultsUsing(fn (string $search) => WhatsAppTemplate::where('tenant_id', auth()->user()->tenant_id)
                                ->where('active', true)
                                ->where('name', 'like', "%{$search}%")
                                ->limit(50)
                                ->pluck('name', 'id'))
                            ->getOptionLabelUsing(fn ($value): ?string => WhatsAppTemplate::find($value)?->name)
                            ->placeholder('Select a template...')
                            ->nullable(),

                        Select::make('automacao_entrada.operador_id')
                            ->label('Entry Automation - Auto-assign Operator')
                            ->helperText('Automatically assign lead to this operator when entering this stage')
                            ->options(fn () => User::where('tenant_id', auth()->user()->tenant_id)
                                ->where('status', 'active')
                                ->whereIn('role', ['admin_client', 'supervisor', 'operator'])
                                ->pluck('name', 'id')
                                ->take(100))
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(fn (string $search) => User::where('tenant_id', auth()->user()->tenant_id)
                                ->where('status', 'active')
                                ->whereIn('role', ['admin_client', 'supervisor', 'operator'])
                                ->where('name', 'like', "%{$search}%")
                                ->limit(50)
                                ->pluck('name', 'id'))
                            ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name)
                            ->placeholder('Select an operator...')
                            ->nullable(),

                        TextInput::make('automacao_entrada.tags')
                            ->label('Entry Automation - Add Tags')
                            ->helperText('Comma-separated tags to add when lead enters this stage')
                            ->placeholder('e.g., contacted,priority')
                            ->nullable(),

                        TextInput::make('automacao_saida.webhook_url')
                            ->label('Exit Automation - Webhook URL')
                            ->url()
                            ->helperText('URL to call when lead exits this stage')
                            ->placeholder('https://example.com/webhook')
                            ->nullable(),
                    ]),
            ]);
    }
}
