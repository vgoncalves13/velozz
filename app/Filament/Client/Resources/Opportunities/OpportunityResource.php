<?php

namespace App\Filament\Client\Resources\Opportunities;

use App\Enums\ClientNavigationGroup;
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

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('opportunities.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('opportunities.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('opportunities.plural');
    }

    protected static string|\UnitEnum|null $navigationGroup = ClientNavigationGroup::Sales;

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
