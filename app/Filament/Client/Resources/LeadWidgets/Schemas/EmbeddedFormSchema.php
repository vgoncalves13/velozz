<?php

namespace App\Filament\Client\Resources\LeadWidgets\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EmbeddedFormSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('lead_widgets.sections.basic_info'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('lead_widgets.labels.name'))
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label(__('lead_widgets.labels.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('lead_widgets.helpers.slug')),

                        Textarea::make('description')
                            ->label(__('lead_widgets.labels.description'))
                            ->rows(3)
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

                        TextInput::make('redirect_url')
                            ->label(__('lead_widgets.labels.redirect_url'))
                            ->url()
                            ->maxLength(500)
                            ->helperText(__('lead_widgets.helpers.redirect_url'))
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make(__('lead_widgets.sections.form_builder'))
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Repeater::make('fields')
                            ->label(__('lead_widgets.labels.fields'))
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->schema([
                                Select::make('type')
                                    ->label(__('lead_widgets.labels.field_type'))
                                    ->options([
                                        'text' => __('lead_widgets.field_types.text'),
                                        'email' => __('lead_widgets.field_types.email'),
                                        'phone' => __('lead_widgets.field_types.phone'),
                                        'number' => __('lead_widgets.field_types.number'),
                                        'textarea' => __('lead_widgets.field_types.textarea'),
                                        'select' => __('lead_widgets.field_types.select'),
                                        'checkbox' => __('lead_widgets.field_types.checkbox'),
                                        'radio' => __('lead_widgets.field_types.radio'),
                                        'file' => __('lead_widgets.field_types.file'),
                                    ])
                                    ->required()
                                    ->live()
                                    ->default('text'),

                                TextInput::make('label')
                                    ->label(__('lead_widgets.labels.field_label'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->afterStateUpdated(fn (string $state, callable $set) => $set('name', Str::slug($state, '_'))),

                                TextInput::make('name')
                                    ->label(__('lead_widgets.labels.field_name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText(__('lead_widgets.helpers.field_name')),

                                TextInput::make('placeholder')
                                    ->label(__('lead_widgets.labels.placeholder'))
                                    ->maxLength(255),

                                Toggle::make('required')
                                    ->label(__('lead_widgets.labels.required'))
                                    ->default(false),

                                TextInput::make('default_value')
                                    ->label(__('lead_widgets.labels.default_value'))
                                    ->maxLength(255),

                                TextInput::make('help_text')
                                    ->label(__('lead_widgets.labels.help_text'))
                                    ->maxLength(500),

                                Repeater::make('options')
                                    ->label(__('lead_widgets.labels.options'))
                                    ->schema([
                                        TextInput::make('value')
                                            ->label(__('lead_widgets.labels.option_value'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->addActionLabel(__('lead_widgets.actions.add_option'))
                                    ->collapsible()
                                    ->visible(fn (callable $get) => in_array($get('type'), ['select', 'radio', 'checkbox'])),

                                Group::make([
                                    TextInput::make('validation.min')
                                        ->label(__('lead_widgets.labels.validation_min'))
                                        ->numeric(),
                                    TextInput::make('validation.max')
                                        ->label(__('lead_widgets.labels.validation_max'))
                                        ->numeric(),
                                    TextInput::make('validation.regex')
                                        ->label(__('lead_widgets.labels.validation_regex'))
                                        ->maxLength(255),
                                ])->columns(3),

                                TextInput::make('order')
                                    ->label(__('lead_widgets.labels.order'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(__('lead_widgets.helpers.order')),
                            ])
                            ->reorderable('order')
                            ->collapsible()
                            ->addActionLabel(__('lead_widgets.actions.add_field'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('lead_widgets.sections.appearance'))
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        TextInput::make('styles.width')
                            ->label(__('lead_widgets.labels.width'))
                            ->default('100%')
                            ->maxLength(50),

                        Select::make('styles.alignment')
                            ->label(__('lead_widgets.labels.alignment'))
                            ->options([
                                'left' => __('lead_widgets.alignments.left'),
                                'center' => __('lead_widgets.alignments.center'),
                                'right' => __('lead_widgets.alignments.right'),
                            ])
                            ->default('left'),

                        Select::make('styles.input_size')
                            ->label(__('lead_widgets.labels.input_size'))
                            ->options([
                                'sm' => __('lead_widgets.sizes.sm'),
                                'md' => __('lead_widgets.sizes.md'),
                                'lg' => __('lead_widgets.sizes.lg'),
                            ])
                            ->default('md'),

                        Select::make('styles.border_radius')
                            ->label(__('lead_widgets.labels.border_radius'))
                            ->options([
                                'none' => __('lead_widgets.border_radius.none'),
                                'sm' => __('lead_widgets.border_radius.sm'),
                                'md' => __('lead_widgets.border_radius.md'),
                                'lg' => __('lead_widgets.border_radius.lg'),
                            ])
                            ->default('md'),

                        ColorPicker::make('styles.text_color')
                            ->label(__('lead_widgets.labels.text_color'))
                            ->default('#000000'),

                        ColorPicker::make('styles.border_color')
                            ->label(__('lead_widgets.labels.border_color'))
                            ->default('#cccccc'),

                        ColorPicker::make('styles.background_color')
                            ->label(__('lead_widgets.labels.background_color'))
                            ->default('#ffffff'),

                        TextInput::make('styles.font_size')
                            ->label(__('lead_widgets.labels.font_size'))
                            ->numeric()
                            ->suffix('pt')
                            ->default(14)
                            ->minValue(8)
                            ->maxValue(72),

                        TextInput::make('styles.button_text')
                            ->label(__('lead_widgets.labels.button_text'))
                            ->default('Submit')
                            ->maxLength(100),

                        ColorPicker::make('styles.button_color')
                            ->label(__('lead_widgets.labels.button_color'))
                            ->default('#3b82f6'),

                        ColorPicker::make('styles.button_text_color')
                            ->label(__('lead_widgets.labels.button_text_color'))
                            ->default('#ffffff'),

                        Select::make('styles.button_size')
                            ->label(__('lead_widgets.labels.button_size'))
                            ->options([
                                'sm' => __('lead_widgets.sizes.sm'),
                                'md' => __('lead_widgets.sizes.md'),
                                'lg' => __('lead_widgets.sizes.lg'),
                            ])
                            ->default('md'),
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

                        TextInput::make('_embed_iframe')
                            ->label(__('lead_widgets.labels.embed_iframe'))
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable()
                            ->extraInputAttributes(['class' => 'font-mono text-xs'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
