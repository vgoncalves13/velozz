<?php

namespace App\Filament\Client\Resources\Opportunities\Pages;

use App\Filament\Client\Resources\Opportunities\OpportunityResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOpportunity extends EditRecord
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Opportunity deleted')
                        ->body('The opportunity has been removed.')
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Opportunity updated successfully')
            ->body('Your changes have been saved.');
    }
}
