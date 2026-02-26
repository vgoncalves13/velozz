<?php

namespace App\Filament\Client\Resources\Opportunities\Pages;

use App\Filament\Client\Resources\Opportunities\OpportunityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpportunity extends EditRecord
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
