<?php

namespace App\Filament\Client\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label(__('fields.photo'))
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=7F9CF5&background=EBF4FF'),

                TextColumn::make('name')
                    ->label(__('fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('fields.email'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('users.messages.email_copied'))
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('phone')
                    ->label(__('fields.phone'))
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->toggleable(),

                TextColumn::make('role')
                    ->label(__('fields.role'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin_client' => 'danger',
                        'supervisor' => 'warning',
                        'operator' => 'success',
                        'financial' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('users.role.'.$state)),

                TextColumn::make('status')
                    ->label(__('fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'invited' => 'warning',
                        'suspended' => 'danger',
                        'temporary' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('users.status.'.$state)),

                TextColumn::make('last_login_at')
                    ->label(__('fields.last_login_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder(__('users.labels.never')),

                TextColumn::make('created_at')
                    ->label(__('users.labels.added'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('fields.role'))
                    ->options([
                        'admin_client' => __('users.role.admin_client'),
                        'supervisor' => __('users.role.supervisor'),
                        'operator' => __('users.role.operator'),
                        'financial' => __('users.role.financial'),
                    ]),

                SelectFilter::make('status')
                    ->label(__('fields.status'))
                    ->options([
                        'active' => __('users.status.active'),
                        'invited' => __('users.status.invited'),
                        'suspended' => __('users.status.suspended'),
                        'temporary' => __('users.status.temporary'),
                    ]),
            ])
            ->recordActions([
                Action::make('send_invite')
                    ->label(__('users.actions.send_invite'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'invited')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        \App\Jobs\SendInviteEmail::dispatch($record);

                        Notification::make()
                            ->title(__('users.notifications.invitation_sent_title'))
                            ->body(__('users.notifications.invitation_sent_body', ['email' => $record->email]))
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

                Action::make('suspend')
                    ->label(__('users.actions.suspend'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'suspended'])),

                Action::make('activate')
                    ->label(__('users.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'suspended')
                    ->action(fn ($record) => $record->update(['status' => 'active'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
