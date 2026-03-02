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
                Section::make('Basic Information')
                    ->description('Define the template name and message content')
                    ->icon('heroicon-o-document-text')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Template Name')
                            ->helperText('Give your template a descriptive name')
                            ->placeholder('e.g., Welcome Message'),

                        Textarea::make('content')
                            ->required()
                            ->rows(6)
                            ->label('Message Content')
                            ->helperText('Use variables like {name}, {company}, {operator}, etc.')
                            ->placeholder('Hello {name}, welcome to {company}! Your operator {operator} will contact you soon.'),
                    ]),

                Section::make('Settings & Automation')
                    ->description('Configure when and how this template should be used')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(1)
                    ->schema([
                        Toggle::make('active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active templates can be used')
                            ->inline(false),

                        Select::make('trigger_on')
                            ->label('Trigger On')
                            ->options([
                                'manual' => 'Manual Only',
                                'lead_created' => 'When Lead is Created',
                                'import' => 'When Lead is Imported',
                                'stage' => 'When Lead Moves to Stage',
                            ])
                            ->default('manual')
                            ->live()
                            ->helperText('When should this template be automatically sent?'),

                        Select::make('pipeline_stage_id')
                            ->label('Pipeline Stage')
                            ->options(fn () => PipelineStage::where('tenant_id', auth()->user()->tenant_id)
                                ->pluck('name', 'id'))
                            ->visible(fn ($get) => $get('trigger_on') === 'stage')
                            ->required(fn ($get) => $get('trigger_on') === 'stage')
                            ->helperText('Template will be sent when lead moves to this stage'),
                    ]),

                Section::make('Available Variables')
                    ->description('You can use these variables in your message content')
                    ->icon('heroicon-o-variable')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        Placeholder::make('variables_info')
                            ->label('')
                            ->content('
                                • **{name}** - Lead\'s full name
                                • **{company}** - Your company name
                                • **{operator}** - Assigned operator name
                                • **{date}** - Current date
                                • **{product}** - Product name (if applicable)
                                • **{link}** - Custom link (if applicable)
                            '),
                    ]),
            ]);
    }
}
