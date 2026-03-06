<?php

namespace App\Filament\Client\Resources\OpportunityStages;

use App\Enums\ClientNavigationGroup;
use App\Filament\Client\Resources\OpportunityStages\Pages\CreateOpportunityStage;
use App\Filament\Client\Resources\OpportunityStages\Pages\EditOpportunityStage;
use App\Filament\Client\Resources\OpportunityStages\Pages\ListOpportunityStages;
use App\Filament\Client\Resources\OpportunityStages\Schemas\OpportunityStageForm;
use App\Filament\Client\Resources\OpportunityStages\Tables\OpportunityStagesTable;
use App\Models\OpportunityStage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OpportunityStageResource extends Resource
{
    protected static ?string $model = OpportunityStage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('opportunity_stages.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('opportunity_stages.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('opportunity_stages.plural');
    }

    protected static string|\UnitEnum|null $navigationGroup = ClientNavigationGroup::Configuration;

    public static function form(Schema $schema): Schema
    {
        return OpportunityStageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpportunityStagesTable::configure($table);
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
            'index' => ListOpportunityStages::route('/'),
            'create' => CreateOpportunityStage::route('/create'),
            'edit' => EditOpportunityStage::route('/{record}/edit'),
        ];
    }
}
