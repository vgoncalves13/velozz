<?php

namespace App\Filament\Client\Resources\Leads\Tables;

use App\Enums\LeadSource;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('fields.email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('primary_whatsapp')
                    ->label(__('fields.whatsapp'))
                    ->getStateUsing(fn ($record) => $record->primary_whatsapp)
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('assignedUser.name')
                    ->label(__('fields.assigned_to'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('pipelineStage.name')
                    ->label(__('fields.stage'))
                    ->badge()
                    ->color(fn ($record) => $record->pipelineStage?->color ?? 'gray')
                    ->toggleable(),

                TextColumn::make('priority')
                    ->label(__('fields.priority'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => __('leads.priority.'.$state))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('source')
                    ->label(__('fields.source'))
                    ->badge()
                    ->color(fn (LeadSource $state): string => match ($state) {
                        LeadSource::Import => 'success',
                        LeadSource::Manual => 'info',
                        LeadSource::Api => 'warning',
                        LeadSource::Form => 'primary',
                        LeadSource::Whatsapp => 'success',
                        LeadSource::Instagram => 'warning',
                        LeadSource::FacebookMessenger => 'info',
                        LeadSource::EmbeddedForm => 'gray',
                        LeadSource::WhatsappWidget => 'green',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (LeadSource $state): string => __('leads.source.'.$state->value))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('city')
                    ->label(__('fields.city'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('opt_out')
                    ->label(__('fields.opted_out'))
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn (bool $state): string => $state ? __('leads.labels.yes') : __('leads.labels.no'))
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('do_not_contact')
                    ->label(__('fields.dnc'))
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn (bool $state): string => $state ? __('leads.labels.yes') : __('leads.labels.no'))
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('updated_at')
                    ->label(__('fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('assigned_user_id')
                    ->label(__('fields.assigned_to'))
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('pipeline_stage_id')
                    ->label(__('fields.pipeline_stage'))
                    ->relationship('pipelineStage', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('source')
                    ->label(__('fields.source'))
                    ->options([
                        'import' => __('leads.source.import'),
                        'manual' => __('leads.source.manual'),
                        'api' => __('leads.source.api'),
                        'form' => __('leads.source.form'),
                        'whatsapp' => __('leads.source.whatsapp'),
                        'instagram' => __('leads.source.instagram'),
                        'facebook_messenger' => __('leads.source.facebook_messenger'),
                    ]),

                SelectFilter::make('priority')
                    ->label(__('fields.priority'))
                    ->options([
                        'low' => __('leads.priority.low'),
                        'medium' => __('leads.priority.medium'),
                        'high' => __('leads.priority.high'),
                        'urgent' => __('leads.priority.urgent'),
                    ]),

                Filter::make('opt_out')
                    ->label(__('fields.opt_out'))
                    ->query(fn (Builder $query): Builder => $query->where('opt_out', true)),

                Filter::make('do_not_contact')
                    ->label(__('fields.do_not_contact'))
                    ->query(fn (Builder $query): Builder => $query->where('do_not_contact', true)),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assign')
                        ->label(__('leads.actions.assign_to_user'))
                        ->icon('heroicon-o-user')
                        ->form([
                            Select::make('assigned_user_id')
                                ->label(__('fields.assigned_to'))
                                ->relationship('assignedUser', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update([
                                'assigned_user_id' => $data['assigned_user_id'],
                            ]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('change_priority')
                        ->label(__('leads.actions.change_priority'))
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority')
                                ->label(__('fields.priority'))
                                ->options([
                                    'low' => __('leads.priority.low'),
                                    'medium' => __('leads.priority.medium'),
                                    'high' => __('leads.priority.high'),
                                    'urgent' => __('leads.priority.urgent'),
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update([
                                'priority' => $data['priority'],
                            ]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('leads.empty_state.heading'))
            ->emptyStateDescription(__('leads.empty_state.description'))
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Action::make('create')
                    ->label(__('leads.actions.create_lead'))
                    ->url(fn (): string => \App\Filament\Client\Resources\Leads\LeadResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
                Action::make('import')
                    ->label(__('leads.actions.import_leads'))
                    ->url('/app/import-leads')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('gray'),
            ]);
    }
}
