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
                Section::make(__('pipeline_stages.sections.basic_information'))
                    ->icon('heroicon-o-information-circle')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->label(__('pipeline_stages.labels.name'))
                            ->helperText(__('pipeline_stages.helper.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        ColorPicker::make('color')
                            ->label(__('pipeline_stages.labels.color'))
                            ->helperText(__('pipeline_stages.helper.color'))
                            ->required()
                            ->default('#3b82f6'),

                        TextInput::make('order')
                            ->label(__('pipeline_stages.labels.order'))
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->helperText(__('pipeline_stages.helper.order')),

                        Select::make('icon')
                            ->label(__('pipeline_stages.labels.icon'))
                            ->helperText(__('pipeline_stages.helper.icon'))
                            ->options([
                                'heroicon-o-inbox' => __('pipeline_stages.icons.inbox'),
                                'heroicon-o-phone' => __('pipeline_stages.icons.phone'),
                                'heroicon-o-chat-bubble-left-right' => __('pipeline_stages.icons.chat'),
                                'heroicon-o-currency-dollar' => __('pipeline_stages.icons.currency_dollar'),
                                'heroicon-o-check-circle' => __('pipeline_stages.icons.check_circle'),
                                'heroicon-o-x-circle' => __('pipeline_stages.icons.x_circle'),
                                'heroicon-o-clock' => __('pipeline_stages.icons.clock'),
                                'heroicon-o-star' => __('pipeline_stages.icons.star'),
                                'heroicon-o-flag' => __('pipeline_stages.icons.flag'),
                            ])
                            ->default('heroicon-o-queue-list')
                            ->columnSpanFull(),

                        TextInput::make('sla_hours')
                            ->label(__('pipeline_stages.labels.sla_hours'))
                            ->numeric()
                            ->nullable()
                            ->minValue(0)
                            ->helperText(__('pipeline_stages.helper.sla_hours'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('pipeline_stages.sections.automations'))
                    ->icon('heroicon-o-bolt')
                    ->columns(1)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Select::make('automacao_entrada.template_id')
                            ->label(__('pipeline_stages.labels.template_id'))
                            ->helperText(__('pipeline_stages.helper.template_id'))
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
                            ->nullable(),

                        Select::make('automacao_entrada.operador_id')
                            ->label(__('pipeline_stages.labels.operador_id'))
                            ->helperText(__('pipeline_stages.helper.operador_id'))
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
                            ->nullable(),

                        TextInput::make('automacao_entrada.tags')
                            ->label(__('pipeline_stages.labels.tags'))
                            ->helperText(__('pipeline_stages.helper.tags'))
                            ->nullable(),

                        TextInput::make('automacao_saida.webhook_url')
                            ->label(__('pipeline_stages.labels.webhook_url'))
                            ->url()
                            ->helperText(__('pipeline_stages.helper.webhook_url'))
                            ->placeholder(__('pipeline_stages.placeholders.webhook_url'))
                            ->nullable(),
                    ]),
            ]);
    }
}
