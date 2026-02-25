<?php

namespace App\Filament\Client\Resources\PipelineStages\Pages;

use App\Filament\Client\Resources\PipelineStages\PipelineStageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPipelineStage extends EditRecord
{
    protected static string $resource = PipelineStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
