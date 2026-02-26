<?php

namespace App\Filament\Client\Resources\Users\Pages;

use App\Filament\Client\Resources\Users\UserResource;
use App\Jobs\SendInviteEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set tenant_id
        $data['tenant_id'] = auth()->user()->tenant_id;

        // Generate a temporary password (will be changed on invite accept)
        $data['password'] = bcrypt(str()->random(32));

        return $data;
    }

    protected function afterCreate(): void
    {
        // Assign Spatie role based on user role field
        $this->record->assignRole($this->record->role);

        // Send invitation email if status is 'invited'
        if ($this->record->status === 'invited') {
            SendInviteEmail::dispatch($this->record);

            Notification::make()
                ->title('User created and invitation sent!')
                ->body("Invitation email sent to {$this->record->email}")
                ->success()
                ->send();
        }
    }
}
