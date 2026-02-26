<?php

namespace App\Filament\Client\Resources\WhatsAppTemplates\Pages;

use App\Filament\Client\Resources\WhatsAppTemplates\WhatsAppTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWhatsAppTemplate extends EditRecord
{
    protected static string $resource = WhatsAppTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
