<?php

namespace App\Filament\Client\Resources\WhatsAppTemplates\Schemas;

use App\Models\PipelineStage;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('whatsapp_templates.sections.basic_information'))
                    ->description(__('whatsapp_templates.sections.basic_information_description'))
                    ->icon('heroicon-o-document-text')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label(__('whatsapp_templates.labels.name'))
                            ->helperText(__('whatsapp_templates.helper.name'))
                            ->placeholder(__('whatsapp_templates.placeholders.name')),

                        Textarea::make('content')
                            ->required()
                            ->rows(6)
                            ->label(__('whatsapp_templates.labels.content'))
                            ->helperText(__('whatsapp_templates.helper.content'))
                            ->placeholder(__('whatsapp_templates.placeholders.content')),
                    ]),

                Section::make(__('whatsapp_templates.sections.settings_automation'))
                    ->description(__('whatsapp_templates.sections.settings_automation_description'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(1)
                    ->schema([
                        Toggle::make('active')
                            ->label(__('whatsapp_templates.labels.active'))
                            ->default(true)
                            ->helperText(__('whatsapp_templates.helper.active'))
                            ->inline(false),

                        Select::make('trigger_on')
                            ->label(__('whatsapp_templates.labels.trigger_on'))
                            ->options([
                                'manual' => __('whatsapp_templates.triggers.manual'),
                                'lead_created' => __('whatsapp_templates.triggers.lead_created'),
                                'import' => __('whatsapp_templates.triggers.import'),
                                'stage' => __('whatsapp_templates.triggers.stage'),
                            ])
                            ->default('manual')
                            ->live()
                            ->helperText(__('whatsapp_templates.helper.trigger_on')),

                        Select::make('pipeline_stage_id')
                            ->label(__('whatsapp_templates.labels.pipeline_stage'))
                            ->options(fn () => PipelineStage::where('tenant_id', auth()->user()->tenant_id)
                                ->pluck('name', 'id'))
                            ->visible(fn ($get) => $get('trigger_on') === 'stage')
                            ->required(fn ($get) => $get('trigger_on') === 'stage')
                            ->helperText(__('whatsapp_templates.helper.pipeline_stage')),
                    ]),

                Section::make(__('whatsapp_templates.sections.available_variables'))
                    ->description(__('whatsapp_templates.sections.available_variables_description'))
                    ->icon('heroicon-o-variable')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        Placeholder::make('variables_info')
                            ->label('')
                            ->content(fn () => '
                                • **{name}** - '.__('whatsapp_templates.variables.name').'
                                • **{company}** - '.__('whatsapp_templates.variables.company').'
                                • **{operator}** - '.__('whatsapp_templates.variables.operator').'
                                • **{date}** - '.__('whatsapp_templates.variables.date').'
                                • **{product}** - '.__('whatsapp_templates.variables.product').'
                                • **{link}** - '.__('whatsapp_templates.variables.link').'
                            '),
                    ]),
            ]);
    }
}
