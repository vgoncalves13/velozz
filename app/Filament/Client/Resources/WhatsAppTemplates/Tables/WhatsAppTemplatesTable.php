<?php

namespace App\Filament\Client\Resources\WhatsAppTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsAppTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('whatsapp_templates.labels.name')),

                TextColumn::make('content')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->content)
                    ->label(__('whatsapp_templates.labels.content')),

                IconColumn::make('active')
                    ->boolean()
                    ->label(__('whatsapp_templates.labels.active')),

                TextColumn::make('trigger_on')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'gray',
                        'lead_created' => 'success',
                        'import' => 'info',
                        'stage' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manual' => __('whatsapp_templates.triggers_formatted.manual'),
                        'lead_created' => __('whatsapp_templates.triggers_formatted.lead_created'),
                        'import' => __('whatsapp_templates.triggers_formatted.import'),
                        'stage' => __('whatsapp_templates.triggers_formatted.stage'),
                        default => $state,
                    })
                    ->label(__('whatsapp_templates.labels.trigger')),

                TextColumn::make('pipelineStage.name')
                    ->label(__('whatsapp_templates.labels.stage'))
                    ->placeholder(__('whatsapp_templates.placeholders.stage_placeholder')),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('whatsapp_templates.labels.created')),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->options([
                        '1' => __('whatsapp_templates.filters.active'),
                        '0' => __('whatsapp_templates.filters.inactive'),
                    ])
                    ->label(__('whatsapp_templates.labels.status')),

                SelectFilter::make('trigger_on')
                    ->options([
                        'manual' => __('whatsapp_templates.triggers_formatted.manual'),
                        'lead_created' => __('whatsapp_templates.triggers_formatted.lead_created'),
                        'import' => __('whatsapp_templates.triggers_formatted.import'),
                        'stage' => __('whatsapp_templates.triggers_formatted.stage'),
                    ])
                    ->label(__('whatsapp_templates.labels.trigger_type')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
