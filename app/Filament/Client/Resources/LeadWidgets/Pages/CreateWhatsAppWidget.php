<?php

namespace App\Filament\Client\Resources\LeadWidgets\Pages;

use App\Filament\Client\Resources\LeadWidgets\WhatsAppWidgetResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsAppWidget extends CreateRecord
{
    protected static string $resource = WhatsAppWidgetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('lead_widgets.whatsapp_widgets.notifications.created'));
    }
}
