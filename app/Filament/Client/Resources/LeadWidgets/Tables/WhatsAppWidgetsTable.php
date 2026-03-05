<?php

namespace App\Filament\Client\Resources\LeadWidgets\Tables;

use App\Filament\Client\Resources\LeadWidgets\WhatsAppWidgetResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WhatsAppWidgetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('lead_widgets.labels.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('whatsapp_number')
                    ->label(__('lead_widgets.labels.whatsapp_number'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('position')
                    ->label(__('lead_widgets.labels.position'))
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bottom-right' => __('lead_widgets.positions.bottom_right'),
                        'bottom-left' => __('lead_widgets.positions.bottom_left'),
                        'top-right' => __('lead_widgets.positions.top_right'),
                        'top-left' => __('lead_widgets.positions.top_left'),
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label(__('lead_widgets.labels.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('lead_widgets.status.active'),
                        'inactive' => __('lead_widgets.status.inactive'),
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label(__('lead_widgets.labels.created'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('lead_widgets.whatsapp_widgets.empty.title'))
            ->emptyStateDescription(__('lead_widgets.whatsapp_widgets.empty.description'))
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateActions([
                Action::make('create')
                    ->label(__('lead_widgets.whatsapp_widgets.actions.create'))
                    ->url(fn (): string => WhatsAppWidgetResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ]);
    }
}
