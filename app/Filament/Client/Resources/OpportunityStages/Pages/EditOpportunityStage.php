<?php

namespace App\Filament\Client\Resources\OpportunityStages\Pages;

use App\Filament\Client\Resources\OpportunityStages\OpportunityStageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpportunityStage extends EditRecord
{
    protected static string $resource = OpportunityStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
