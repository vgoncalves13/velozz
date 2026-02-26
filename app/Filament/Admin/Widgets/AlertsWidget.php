<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use App\Models\WhatsAppInstance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class AlertsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Get tenants with issues
        $tenantsWithIssues = collect();

        // Disconnected WhatsApp instances
        $disconnectedInstances = WhatsAppInstance::whereIn('status', ['disconnected', 'error'])
            ->with('tenant')
            ->get();

        foreach ($disconnectedInstances as $instance) {
            $tenantsWithIssues->push([
                'tenant_id' => $instance->tenant_id,
                'tenant_name' => $instance->tenant->name,
                'type' => 'WhatsApp Disconnected',
                'severity' => 'high',
                'message' => 'WhatsApp instance is disconnected or has errors',
            ]);
        }

        // Tenants with trial expiring soon (next 7 days)
        $expiringTrials = Tenant::where('status', 'trial')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
            ->get();

        foreach ($expiringTrials as $tenant) {
            $daysLeft = now()->diffInDays($tenant->trial_ends_at);
            $tenantsWithIssues->push([
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'type' => 'Trial Expiring',
                'severity' => 'medium',
                'message' => "Trial expires in {$daysLeft} days",
            ]);
        }

        // Tenants with expired trials
        $expiredTrials = Tenant::where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expiredTrials as $tenant) {
            $tenantsWithIssues->push([
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'type' => 'Trial Expired',
                'severity' => 'high',
                'message' => 'Trial period has expired',
            ]);
        }

        return $table
            ->query(
                // Use a dummy query - we'll override with actual data
                Tenant::query()->whereRaw('1 = 0')
            )
            ->columns([
                TextColumn::make('tenant_name')
                    ->label('Tenant')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'WhatsApp Disconnected' => 'danger',
                        'Trial Expired' => 'danger',
                        'Trial Expiring' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('message')
                    ->label('Details')
                    ->wrap(),
            ])
            ->paginated(false)
            ->heading('System Alerts')
            ->description('Issues requiring attention')
            ->records($tenantsWithIssues);
    }
}
