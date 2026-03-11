<?php

namespace App\Filament\Client\Widgets;

use App\Models\User;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TeamPerformanceWidget extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->where('status', 'active')
                    ->withCount([
                        'assignedLeads as assigned_leads_count',
                        'sentMessages as sent_messages_count',
                    ])
                    ->withCount([
                        'assignedLeads as received_messages_count' => function ($query) {
                            $query->whereHas('whatsappMessages', function ($q) {
                                $q->where('direction', 'incoming');
                            });
                        },
                    ])
            )
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=7F9CF5&background=EBF4FF'),

                TextColumn::make('name')
                    ->label('Operator')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin_client' => 'danger',
                        'supervisor' => 'warning',
                        'operator' => 'success',
                        'financial' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_'))),

                TextColumn::make('assigned_leads_count')
                    ->label('Assigned Leads')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('sent_messages_count')
                    ->label('Messages Sent')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('received_messages_count')
                    ->label('Responses')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('response_rate')
                    ->label('Response Rate')
                    ->alignCenter()
                    ->badge()
                    ->state(function ($record) {
                        if ($record->sent_messages_count == 0) {
                            return 'N/A';
                        }

                        $rate = round(($record->received_messages_count / $record->sent_messages_count) * 100);

                        return $rate.'%';
                    })
                    ->color(function ($record) {
                        if ($record->sent_messages_count == 0) {
                            return 'gray';
                        }

                        $rate = ($record->received_messages_count / $record->sent_messages_count) * 100;

                        return match (true) {
                            $rate >= 70 => 'success',
                            $rate >= 40 => 'warning',
                            default => 'danger',
                        };
                    }),

                TextColumn::make('last_login_at')
                    ->label('Last Active')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Never')
                    ->toggleable(),
            ])
            ->defaultSort('sent_messages_count', 'desc')
            ->heading('Team Performance')
            ->description('Performance metrics for all active team members');
    }
}
