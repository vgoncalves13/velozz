<?php

namespace App\Filament\Client\Resources\WhatsAppTemplates\Schemas;

use App\Models\PipelineStage;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WhatsAppTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Template Name')
                    ->helperText('Give your template a descriptive name'),

                Textarea::make('content')
                    ->required()
                    ->rows(5)
                    ->label('Message Content')
                    ->helperText('Available variables: {name}, {company}, {operator}, {link}, {date}, {product}')
                    ->placeholder('Hello {name}, welcome to {company}! Your operator {operator} will contact you soon.'),

                Toggle::make('active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Only active templates can be used'),

                Select::make('trigger_on')
                    ->label('Trigger On')
                    ->options([
                        'manual' => 'Manual Only',
                        'lead_created' => 'When Lead is Created',
                        'import' => 'When Lead is Imported',
                        'stage' => 'When Lead Moves to Stage',
                    ])
                    ->default('manual')
                    ->reactive()
                    ->helperText('When should this template be automatically sent?'),

                Select::make('pipeline_stage_id')
                    ->label('Pipeline Stage')
                    ->options(fn () => PipelineStage::where('tenant_id', auth()->user()->tenant_id)
                        ->pluck('name', 'id'))
                    ->hidden(fn ($get) => $get('trigger_on') !== 'stage')
                    ->helperText('Template will be sent when lead moves to this stage'),

                Placeholder::make('variables_info')
                    ->label('Available Variables')
                    ->content('
                        • {name} - Lead\'s full name
                        • {company} - Your company name
                        • {operator} - Assigned operator name
                        • {date} - Current date
                        • {product} - Product name (if applicable)
                        • {link} - Custom link (if applicable)
                    ')
                    ->columnSpanFull(),
            ]);
    }
}
