<?php

namespace App\Filament\Client\Resources\OpportunityStages\Pages;

use App\Filament\Client\Resources\OpportunityStages\OpportunityStageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpportunityStages extends ListRecords
{
    protected static string $resource = OpportunityStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
