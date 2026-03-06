<?php

namespace App\Filament\Client\Resources\LeadWidgets;

use App\Enums\ClientNavigationGroup;
use App\Filament\Client\Resources\LeadWidgets\Pages\CreateEmbeddedForm;
use App\Filament\Client\Resources\LeadWidgets\Pages\EditEmbeddedForm;
use App\Filament\Client\Resources\LeadWidgets\Pages\ListEmbeddedForms;
use App\Filament\Client\Resources\LeadWidgets\Schemas\EmbeddedFormSchema;
use App\Filament\Client\Resources\LeadWidgets\Tables\EmbeddedFormsTable;
use App\Models\EmbeddedForm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmbeddedFormResource extends Resource
{
    protected static ?string $model = EmbeddedForm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('lead_widgets.embedded_forms.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('lead_widgets.embedded_forms.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lead_widgets.embedded_forms.plural');
    }

    protected static string|\UnitEnum|null $navigationGroup = ClientNavigationGroup::LeadWidgets;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return EmbeddedFormSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmbeddedFormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmbeddedForms::route('/'),
            'create' => CreateEmbeddedForm::route('/create'),
            'edit' => EditEmbeddedForm::route('/{record}/edit'),
        ];
    }
}
