<?php

namespace App\Filament\Client\Resources\Users\Pages;

use App\Filament\Client\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Sync Spatie role when role is updated
        $this->record->syncRoles([$this->record->role]);
    }
}
