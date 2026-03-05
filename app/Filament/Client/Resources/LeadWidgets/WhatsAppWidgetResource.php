<?php

namespace App\Filament\Client\Resources\LeadWidgets;

use App\Filament\Client\Resources\LeadWidgets\Pages\CreateWhatsAppWidget;
use App\Filament\Client\Resources\LeadWidgets\Pages\EditWhatsAppWidget;
use App\Filament\Client\Resources\LeadWidgets\Pages\ListWhatsAppWidgets;
use App\Filament\Client\Resources\LeadWidgets\Schemas\WhatsAppWidgetSchema;
use App\Filament\Client\Resources\LeadWidgets\Tables\WhatsAppWidgetsTable;
use App\Models\WhatsAppWidget;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsAppWidgetResource extends Resource
{
    protected static ?string $model = WhatsAppWidget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('lead_widgets.whatsapp_widgets.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('lead_widgets.whatsapp_widgets.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lead_widgets.whatsapp_widgets.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.lead_widgets');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return WhatsAppWidgetSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppWidgetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsAppWidgets::route('/'),
            'create' => CreateWhatsAppWidget::route('/create'),
            'edit' => EditWhatsAppWidget::route('/{record}/edit'),
        ];
    }
}
