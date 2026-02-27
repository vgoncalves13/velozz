<?php

namespace App\Filament\Client\Resources\Leads\Tables;

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
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('primary_whatsapp')
                    ->label('WhatsApp')
                    ->getStateUsing(fn ($record) => $record->primary_whatsapp)
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('pipelineStage.name')
                    ->label('Stage')
                    ->badge()
                    ->color(fn ($record) => $record->pipelineStage?->color ?? 'gray')
                    ->toggleable(),

                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'import' => 'success',
                        'manual' => 'info',
                        'api' => 'warning',
                        'form' => 'primary',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('opt_out')
                    ->label('Opted Out')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('do_not_contact')
                    ->label('DNC')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('assigned_user_id')
                    ->label('Assigned To')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('pipeline_stage_id')
                    ->label('Pipeline Stage')
                    ->relationship('pipelineStage', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('source')
                    ->options([
                        'import' => 'Import',
                        'manual' => 'Manual',
                        'api' => 'API',
                        'form' => 'Form',
                    ]),

                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                Filter::make('opt_out')
                    ->label('Opted Out')
                    ->query(fn (Builder $query): Builder => $query->where('opt_out', true)),

                Filter::make('do_not_contact')
                    ->label('Do Not Contact')
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
                        ->label('Assign to User')
                        ->icon('heroicon-o-user')
                        ->form([
                            Select::make('assigned_user_id')
                                ->label('Assign To')
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
                        ->label('Change Priority')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority')
                                ->options([
                                    'low' => 'Low',
                                    'medium' => 'Medium',
                                    'high' => 'High',
                                    'urgent' => 'Urgent',
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
            ->emptyStateHeading('No leads yet')
            ->emptyStateDescription('Start building your pipeline by creating your first lead or importing from a spreadsheet.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create Lead')
                    ->url(fn (): string => \App\Filament\Client\Resources\Leads\LeadResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
                Action::make('import')
                    ->label('Import Leads')
                    ->url('/app/import-leads')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('gray'),
            ]);
    }
}
