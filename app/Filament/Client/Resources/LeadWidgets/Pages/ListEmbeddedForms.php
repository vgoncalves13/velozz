<?php

namespace App\Filament\Client\Resources\LeadWidgets\Pages;

use App\Filament\Client\Resources\LeadWidgets\EmbeddedFormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmbeddedForms extends ListRecords
{
    protected static string $resource = EmbeddedFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
