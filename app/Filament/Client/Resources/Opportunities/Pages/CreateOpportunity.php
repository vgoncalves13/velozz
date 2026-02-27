<?php

namespace App\Filament\Client\Resources\Opportunities\Pages;

use App\Filament\Client\Resources\Opportunities\OpportunityResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOpportunity extends CreateRecord
{
    protected static string $resource = OpportunityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Opportunity created successfully')
            ->body('The opportunity has been added to your sales funnel.');
    }
}
