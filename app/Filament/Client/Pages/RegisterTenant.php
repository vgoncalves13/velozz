<?php

namespace App\Filament\Client\Pages;

use App\Models\Tenant;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant as BaseRegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RegisterTenant extends BaseRegisterTenant
{
    public static function getLabel(): string
    {
        return __('tenant.register_label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('tenant.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    }),

                TextInput::make('slug')
                    ->label(__('tenant.fields.slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique(Tenant::class, 'slug')
                    ->alphaDash(),
            ]);
    }

    protected function handleRegistration(array $data): Tenant
    {
        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'domain' => $data['slug'].'.'.config('app.domain', 'velozz.digital'),
            'status' => 'trial',
        ]);

        $user = auth()->user();
        $user->update(['tenant_id' => $tenant->id]);

        return $tenant;
    }
}
