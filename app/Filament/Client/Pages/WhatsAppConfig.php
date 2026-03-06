<?php

namespace App\Filament\Client\Pages;

use App\Enums\ClientNavigationGroup;
use App\Helpers\AuditHelper;
use App\Models\WhatsAppInstance;
use App\Services\ZApi\ZApiServiceInterface;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class WhatsAppConfig extends Page
{
    protected string $view = 'filament.client.pages.whatsapp-config';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('whatsapp_config.navigation');
    }

    public function getTitle(): string
    {
        return __('whatsapp_config.title');
    }

    protected static string|\UnitEnum|null $navigationGroup = ClientNavigationGroup::WhatsApp;

    public ?WhatsAppInstance $instance = null;

    public function mount(): void
    {
        $this->instance = WhatsAppInstance::where('tenant_id', auth()->user()->tenant_id)->first();
    }

    protected function getHeaderActions(): array
    {
        if (! $this->instance) {
            return [
                Action::make('create_instance')
                    ->label(__('whatsapp_config.actions.create_instance'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('instance_id')
                            ->required()
                            ->label(__('whatsapp_config.form.instance_id'))
                            ->helperText(__('whatsapp_config.form.instance_id_helper')),

                        TextInput::make('token')
                            ->required()
                            ->label(__('whatsapp_config.form.token'))
                            ->helperText(__('whatsapp_config.form.token_helper')),
                    ])
                    ->action(function (array $data) {
                        $this->instance = WhatsAppInstance::create([
                            'tenant_id' => auth()->user()->tenant_id,
                            'instance_id' => $data['instance_id'],
                            'token' => $data['token'],
                            'status' => 'disconnected',
                        ]);

                        Notification::make()
                            ->title(__('whatsapp_config.notifications.instance_created_title'))
                            ->body(__('whatsapp_config.notifications.instance_created_body'))
                            ->success()
                            ->send();

                        $this->redirect(static::getUrl());
                    }),
            ];
        }

        $actions = [];

        if ($this->instance->needsQrCode()) {
            $actions[] = Action::make('connect')
                ->label(__('whatsapp_config.actions.connect'))
                ->icon('heroicon-o-qr-code')
                ->action(function (ZApiServiceInterface $zapi) {
                    $response = $zapi->generateQrCode($this->instance->instance_id);

                    $this->instance->update([
                        'status' => 'connecting',
                        'qr_code' => $response['qrcode'],
                    ]);

                    // Log QR code access
                    AuditHelper::log('qr_code_access', 'whatsapp_instance', $this->instance->id, null, [
                        'instance_id' => $this->instance->instance_id,
                        'action' => 'generated',
                    ]);

                    Notification::make()
                        ->title(__('whatsapp_config.notifications.qr_generated_title'))
                        ->body(__('whatsapp_config.notifications.qr_generated_body'))
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                });
        }

        if ($this->instance->isConnected()) {
            $actions[] = Action::make('disconnect')
                ->label(__('whatsapp_config.actions.disconnect'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (ZApiServiceInterface $zapi) {
                    $zapi->disconnect($this->instance->instance_id);

                    $this->instance->update([
                        'status' => 'disconnected',
                        'qr_code' => null,
                        'phone_number' => null,
                    ]);

                    Notification::make()
                        ->title(__('whatsapp_config.notifications.disconnected_title'))
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                });
        }

        $actions[] = Action::make('check_status')
            ->label(__('whatsapp_config.actions.check_status'))
            ->icon('heroicon-o-arrow-path')
            ->action(function (ZApiServiceInterface $zapi) {
                $status = $zapi->getConnectionStatus($this->instance->instance_id);

                if ($status['status'] === 'connected') {
                    $this->instance->update([
                        'status' => 'connected',
                        'phone_number' => $status['phone'],
                        'qr_code' => null,
                        'last_connected_at' => now(),
                    ]);

                    Notification::make()
                        ->title(__('whatsapp_config.notifications.connected_title'))
                        ->body(__('whatsapp_config.notifications.connected_body', ['phone' => $status['phone']]))
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title(__('whatsapp_config.notifications.not_connected_title'))
                        ->warning()
                        ->send();
                }

                $this->redirect(static::getUrl());
            });

        return $actions;
    }
}
