<?php

namespace App\Filament\Client\Resources\LeadWidgets\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppWidgetSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('lead_widgets.sections.config'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('lead_widgets.labels.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('whatsapp_number')
                            ->label(__('lead_widgets.labels.whatsapp_number'))
                            ->required()
                            ->maxLength(30)
                            ->helperText(__('lead_widgets.helpers.whatsapp_number'))
                            ->placeholder('+351912345678'),

                        Textarea::make('auto_message')
                            ->label(__('lead_widgets.labels.auto_message'))
                            ->required()
                            ->rows(3)
                            ->helperText(__('lead_widgets.helpers.auto_message'))
                            ->columnSpanFull(),

                        ToggleButtons::make('status')
                            ->label(__('lead_widgets.labels.status'))
                            ->options([
                                'active' => __('lead_widgets.status.active'),
                                'inactive' => __('lead_widgets.status.inactive'),
                            ])
                            ->colors([
                                'active' => 'success',
                                'inactive' => 'gray',
                            ])
                            ->default('active')
                            ->required()
                            ->inline(),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make(__('lead_widgets.sections.position_appearance'))
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        Select::make('position')
                            ->label(__('lead_widgets.labels.position'))
                            ->options([
                                'bottom-right' => __('lead_widgets.positions.bottom_right'),
                                'bottom-left' => __('lead_widgets.positions.bottom_left'),
                                'top-right' => __('lead_widgets.positions.top_right'),
                                'top-left' => __('lead_widgets.positions.top_left'),
                            ])
                            ->default('bottom-right')
                            ->required(),

                        ColorPicker::make('appearance.button_color')
                            ->label(__('lead_widgets.labels.button_color'))
                            ->default('#25D366'),

                        Select::make('appearance.button_size')
                            ->label(__('lead_widgets.labels.button_size'))
                            ->options([
                                '48px' => __('lead_widgets.sizes.sm'),
                                '60px' => __('lead_widgets.sizes.md'),
                                '72px' => __('lead_widgets.sizes.lg'),
                            ])
                            ->default('60px'),

                        Select::make('appearance.border_radius')
                            ->label(__('lead_widgets.labels.border_radius'))
                            ->options([
                                '0' => __('lead_widgets.border_radius.none'),
                                '8px' => __('lead_widgets.border_radius.sm'),
                                '16px' => __('lead_widgets.border_radius.md'),
                                '50%' => __('lead_widgets.border_radius.full'),
                            ])
                            ->default('50%'),

                        Select::make('appearance.animation')
                            ->label(__('lead_widgets.labels.animation'))
                            ->options([
                                'none' => __('lead_widgets.animations.none'),
                                'pulse' => __('lead_widgets.animations.pulse'),
                                'bounce' => __('lead_widgets.animations.bounce'),
                            ])
                            ->default('none'),

                        TextInput::make('appearance.button_text')
                            ->label(__('lead_widgets.labels.button_text_label'))
                            ->maxLength(50)
                            ->helperText(__('lead_widgets.helpers.button_text')),

                        Toggle::make('appearance.show_text')
                            ->label(__('lead_widgets.labels.show_text'))
                            ->default(false),
                    ])
                    ->columns(['default' => 1, 'md' => 2])
                    ->collapsible(),

                Section::make(__('lead_widgets.sections.embed_code'))
                    ->icon('heroicon-o-code-bracket')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->schema([
                        TextInput::make('_embed_script')
                            ->label(__('lead_widgets.labels.embed_script'))
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable()
                            ->extraInputAttributes(['class' => 'font-mono text-xs'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
