<?php

namespace App\Filament\Client\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('An invitation email will be sent to this address'),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),

                        FileUpload::make('photo')
                            ->image()
                            ->imageEditor()
                            ->directory('team-photos')
                            ->visibility('private')
                            ->maxSize(2048),
                    ])
                    ->columns(2),

                Section::make('Role & Access')
                    ->schema([
                        Select::make('role')
                            ->options([
                                'admin_client' => 'Admin Client',
                                'supervisor' => 'Supervisor',
                                'operator' => 'Operator',
                                'financial' => 'Financial',
                            ])
                            ->default('operator')
                            ->required()
                            ->helperText('Admin Client: Full access | Supervisor: Manage team | Operator: Handle leads | Financial: View reports'),

                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'invited' => 'Invited',
                                'suspended' => 'Suspended',
                                'temporary' => 'Temporary',
                            ])
                            ->default('invited')
                            ->required()
                            ->helperText('Invited: User will receive email to set password | Active: User can login'),
                    ])
                    ->columns(2),
            ]);
    }
}
