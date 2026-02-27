<?php

namespace App\Filament\Client\Resources\Users;

use App\Filament\Client\Resources\Users\Pages\CreateUser;
use App\Filament\Client\Resources\Users\Pages\EditUser;
use App\Filament\Client\Resources\Users\Pages\ListUsers;
use App\Filament\Client\Resources\Users\Schemas\UserForm;
use App\Filament\Client\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Team';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuration';

    protected static ?string $modelLabel = 'Operator';

    protected static ?string $pluralModelLabel = 'Operators';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereNotNull('tenant_id'); // Exclude admin_master users
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
