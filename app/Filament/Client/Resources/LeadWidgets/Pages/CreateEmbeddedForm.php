<?php

namespace App\Filament\Client\Resources\LeadWidgets\Pages;

use App\Filament\Client\Resources\LeadWidgets\EmbeddedFormResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEmbeddedForm extends CreateRecord
{
    protected static string $resource = EmbeddedFormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('lead_widgets.embedded_forms.notifications.created'));
    }
}
