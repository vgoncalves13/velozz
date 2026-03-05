<?php

namespace App\Filament\Client\Resources\LeadWidgets\Pages;

use App\Filament\Client\Resources\LeadWidgets\WhatsAppWidgetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppWidgets extends ListRecords
{
    protected static string $resource = WhatsAppWidgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
