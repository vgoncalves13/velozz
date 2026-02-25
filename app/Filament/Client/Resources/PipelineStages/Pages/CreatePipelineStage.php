<?php

namespace App\Filament\Client\Resources\PipelineStages\Pages;

use App\Filament\Client\Resources\PipelineStages\PipelineStageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePipelineStage extends CreateRecord
{
    protected static string $resource = PipelineStageResource::class;
}
