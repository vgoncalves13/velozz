<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Jobs\SendInviteEmail;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function afterCreate(): void
    {
        $tenant = $this->record;
        $data = $this->data;

        if (empty($data['admin_email'])) {
            return;
        }

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'phone' => $data['admin_phone'] ?? null,
            'password' => Hash::make(Str::random(32)),
            'role' => 'admin_client',
            'status' => 'invited',
        ]);

        SendInviteEmail::dispatch($admin);
    }
}
