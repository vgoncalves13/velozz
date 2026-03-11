<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tenant Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                    $set('domain', Str::slug($state).'.velozz.digital');
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('Unique identifier for the tenant'),

                        Forms\Components\TextInput::make('domain')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Full domain (e.g., client1.velozz.digital)'),

                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'trial' => 'Trial',
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                                'blocked' => 'Blocked',
                            ])
                            ->default('trial'),
                    ])
                    ->columns(2),

                Section::make('Subscription')
                    ->schema([
                        Forms\Components\Select::make('plan_id')
                            ->relationship('plan', 'name')
                            ->nullable()
                            ->helperText('Leave empty for default plan'),

                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('subscription_ends_at')
                            ->nullable(),
                    ])
                    ->columns(3),

                Section::make('Admin Details')
                    ->schema([
                        Forms\Components\TextInput::make('admin_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('admin_email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('admin_phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Settings')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->nullable()
                            ->helperText('JSON settings: working hours, logo, colors, webhooks'),
                    ])
                    ->collapsed(),
            ]);
    }
}
