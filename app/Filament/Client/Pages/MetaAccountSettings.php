<?php

namespace App\Filament\Client\Pages;

use App\Enums\Channel;
use App\Models\MetaAccount;
use App\Services\Meta\MetaGraphApiServiceInterface;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MetaAccountSettings extends Page
{
    protected string $view = 'filament.client.pages.meta-account-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('meta_settings.navigation');
    }

    public function getTitle(): string
    {
        return __('meta_settings.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.integrations');
    }

    public function getAccounts()
    {
        return MetaAccount::where('tenant_id', auth()->user()->tenant_id)->get();
    }

    public function getWebhookUrl(): string
    {
        return url('/api/webhook/meta');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connectFacebook')
                ->label(__('meta_settings.actions.connect_facebook'))
                ->icon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(fn () => route('meta.oauth.redirect')),

            Action::make('addAccount')
                ->label(__('meta_settings.actions.add_account'))
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->form([
                    Select::make('type')
                        ->label(__('meta_settings.form.type'))
                        ->options([
                            Channel::Instagram->value => 'Instagram',
                            Channel::FacebookMessenger->value => 'Facebook Messenger',
                        ])
                        ->required(),

                    TextInput::make('page_id')
                        ->label(__('meta_settings.form.page_id'))
                        ->required()
                        ->helperText(__('meta_settings.form.page_id_helper')),

                    TextInput::make('page_name')
                        ->label(__('meta_settings.form.page_name'))
                        ->required(),

                    TextInput::make('instagram_user_id')
                        ->label(__('meta_settings.form.instagram_user_id'))
                        ->helperText(__('meta_settings.form.instagram_user_id_helper')),

                    TextInput::make('access_token')
                        ->label(__('meta_settings.form.access_token'))
                        ->password()
                        ->required()
                        ->helperText(__('meta_settings.form.access_token_helper')),
                ])
                ->action(function (array $data, MetaGraphApiServiceInterface $metaApi): void {
                    // Validate token before saving
                    $result = $metaApi->validateToken($data['page_id'], $data['access_token']);

                    if (! ($result['success'] ?? false)) {
                        Notification::make()
                            ->title(__('meta_settings.notifications.invalid_token_title'))
                            ->body($result['error'] ?? __('meta_settings.notifications.invalid_token_body'))
                            ->danger()
                            ->send();

                        return;
                    }

                    MetaAccount::create([
                        'tenant_id' => auth()->user()->tenant_id,
                        'type' => $data['type'],
                        'page_id' => $data['page_id'],
                        'page_name' => $data['page_name'],
                        'instagram_user_id' => $data['instagram_user_id'] ?? null,
                        'access_token' => $data['access_token'],
                        'status' => 'connected',
                    ]);

                    Notification::make()
                        ->title(__('meta_settings.notifications.account_connected_title'))
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }

    public function disconnect(int $accountId): void
    {
        $account = MetaAccount::where('id', $accountId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $account->update(['status' => 'disconnected']);

        Notification::make()
            ->title(__('meta_settings.notifications.account_disconnected_title'))
            ->success()
            ->send();
    }

    public function delete(int $accountId): void
    {
        $account = MetaAccount::where('id', $accountId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $account->delete();

        Notification::make()
            ->title(__('meta_settings.notifications.account_deleted_title'))
            ->success()
            ->send();
    }
}
