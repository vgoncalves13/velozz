<?php

namespace App\Filament\Client\Resources\Leads\Pages;

use App\Filament\Client\Resources\Leads\LeadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Lead deleted')
                        ->body('The lead has been moved to trash.')
                ),
            ForceDeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Lead permanently deleted')
                        ->body('The lead has been permanently removed.')
                ),
            RestoreAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Lead restored')
                        ->body('The lead has been restored from trash.')
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Lead updated successfully')
            ->body('Your changes have been saved.');
    }
}
