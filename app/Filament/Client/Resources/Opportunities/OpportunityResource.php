<?php

namespace App\Filament\Client\Resources\Opportunities;

use App\Filament\Client\Resources\Opportunities\Pages\CreateOpportunity;
use App\Filament\Client\Resources\Opportunities\Pages\EditOpportunity;
use App\Filament\Client\Resources\Opportunities\Pages\ListOpportunities;
use App\Filament\Client\Resources\Opportunities\Schemas\OpportunityForm;
use App\Filament\Client\Resources\Opportunities\Tables\OpportunitiesTable;
use App\Models\Opportunity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Opportunities';

    protected static ?int $navigationSort = 7;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return OpportunityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpportunitiesTable::configure($table);
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
            'index' => ListOpportunities::route('/'),
            'create' => CreateOpportunity::route('/create'),
            'edit' => EditOpportunity::route('/{record}/edit'),
        ];
    }
}
