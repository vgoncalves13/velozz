<?php

namespace App\Filament\Client\Resources\WhatsAppTemplates\Pages;

use App\Filament\Client\Resources\WhatsAppTemplates\WhatsAppTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppTemplates extends ListRecords
{
    protected static string $resource = WhatsAppTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
