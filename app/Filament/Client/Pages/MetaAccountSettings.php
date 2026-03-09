<?php

namespace App\Filament\Client\Pages;

use App\Enums\ClientNavigationGroup;
use App\Models\MetaAccount;
use BackedEnum;
use Filament\Actions\Action;
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

    protected static string|\UnitEnum|null $navigationGroup = ClientNavigationGroup::Integrations;

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
            Action::make('connectInstagram')
                ->label(__('meta_settings.actions.connect_instagram'))
                ->icon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(fn () => route('instagram.oauth.redirect')),

            Action::make('connectFacebook')
                ->label(__('meta_settings.actions.connect_facebook'))
                ->icon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(fn () => route('meta.oauth.redirect')),

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
