<?php

namespace App\Filament\Client\Resources\WhatsAppTemplates;

use App\Filament\Client\Resources\WhatsAppTemplates\Pages\CreateWhatsAppTemplate;
use App\Filament\Client\Resources\WhatsAppTemplates\Pages\EditWhatsAppTemplate;
use App\Filament\Client\Resources\WhatsAppTemplates\Pages\ListWhatsAppTemplates;
use App\Filament\Client\Resources\WhatsAppTemplates\Schemas\WhatsAppTemplateForm;
use App\Filament\Client\Resources\WhatsAppTemplates\Tables\WhatsAppTemplatesTable;
use App\Models\WhatsAppTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsAppTemplateResource extends Resource
{
    protected static ?string $model = WhatsAppTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('whatsapp_templates.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('whatsapp_templates.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('whatsapp_templates.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'WhatsApp';
    }

    public static function form(Schema $schema): Schema
    {
        return WhatsAppTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppTemplatesTable::configure($table);
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
            'index' => ListWhatsAppTemplates::route('/'),
            'create' => CreateWhatsAppTemplate::route('/create'),
            'edit' => EditWhatsAppTemplate::route('/{record}/edit'),
        ];
    }
}
