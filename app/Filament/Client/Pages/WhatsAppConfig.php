<?php

namespace App\Filament\Client\Pages;

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

    protected static ?string $navigationLabel = 'WhatsApp';

    protected static ?string $title = 'WhatsApp Configuration';

    protected static ?int $navigationSort = 3;

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
                    ->label('Create Instance')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('instance_id')
                            ->required()
                            ->label('Instance ID')
                            ->helperText('Your Z-API instance ID'),

                        TextInput::make('token')
                            ->required()
                            ->label('Token')
                            ->helperText('Your Z-API token'),
                    ])
                    ->action(function (array $data) {
                        $this->instance = WhatsAppInstance::create([
                            'tenant_id' => auth()->user()->tenant_id,
                            'instance_id' => $data['instance_id'],
                            'token' => $data['token'],
                            'status' => 'disconnected',
                        ]);

                        Notification::make()
                            ->title('Instance created!')
                            ->body('Now you can connect your WhatsApp')
                            ->success()
                            ->send();

                        $this->redirect(static::getUrl());
                    }),
            ];
        }

        $actions = [];

        if ($this->instance->needsQrCode()) {
            $actions[] = Action::make('connect')
                ->label('Connect WhatsApp')
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
                        ->title('QR Code generated!')
                        ->body('Scan the QR code with your WhatsApp')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                });
        }

        if ($this->instance->isConnected()) {
            $actions[] = Action::make('disconnect')
                ->label('Disconnect')
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
                        ->title('Disconnected')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                });
        }

        $actions[] = Action::make('check_status')
            ->label('Check Status')
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
                        ->title('Connected!')
                        ->body("Phone: {$status['phone']}")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Not connected yet')
                        ->warning()
                        ->send();
                }

                $this->redirect(static::getUrl());
            });

        return $actions;
    }
}
