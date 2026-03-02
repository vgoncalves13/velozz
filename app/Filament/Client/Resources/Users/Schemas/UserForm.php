<?php

namespace App\Filament\Client\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('users.sections.basic_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('fields.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('users.labels.email_address'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText(__('users.helper.email')),

                        TextInput::make('phone')
                            ->label(__('fields.phone'))
                            ->tel()
                            ->maxLength(20),

                        FileUpload::make('photo')
                            ->label(__('fields.photo'))
                            ->image()
                            ->imageEditor()
                            ->directory('team-photos')
                            ->visibility('private')
                            ->maxSize(2048),
                    ])
                    ->columns(2),

                Section::make(__('users.sections.role_access'))
                    ->schema([
                        Select::make('role')
                            ->label(__('fields.role'))
                            ->options([
                                'admin_client' => __('users.role.admin_client'),
                                'supervisor' => __('users.role.supervisor'),
                                'operator' => __('users.role.operator'),
                                'financial' => __('users.role.financial'),
                            ])
                            ->default('operator')
                            ->required()
                            ->helperText(__('users.helper.role')),

                        Select::make('status')
                            ->label(__('fields.status'))
                            ->options([
                                'active' => __('users.status.active'),
                                'invited' => __('users.status.invited'),
                                'suspended' => __('users.status.suspended'),
                                'temporary' => __('users.status.temporary'),
                            ])
                            ->default('invited')
                            ->required()
                            ->helperText(__('users.helper.status')),
                    ])
                    ->columns(2),
            ]);
    }
}
