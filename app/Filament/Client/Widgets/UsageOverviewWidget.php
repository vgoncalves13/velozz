<?php

namespace App\Filament\Client\Widgets;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsageOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $tenant = Tenant::find(auth()->user()->tenant_id);
        $plan = $tenant->plan;

        if (! $plan) {
            return [
                Stat::make('No Plan', 'Please contact administrator to assign a plan')
                    ->description('No plan assigned')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        $leadsThisMonth = Lead::where('tenant_id', $tenant->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $messagesToday = WhatsAppMessage::where('tenant_id', $tenant->id)
            ->where('direction', 'outgoing')
            ->whereDate('created_at', today())
            ->count();

        $activeOperators = User::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->count();

        $whatsappInstances = WhatsAppInstance::where('tenant_id', $tenant->id)->count();

        $leadsPercentage = $plan->leads_limit_per_month > 0
            ? round(($leadsThisMonth / $plan->leads_limit_per_month) * 100, 1)
            : 0;

        $messagesPercentage = $plan->messages_limit_per_day > 0
            ? round(($messagesToday / $plan->messages_limit_per_day) * 100, 1)
            : 0;

        $operatorsPercentage = $plan->operators_limit > 0
            ? round(($activeOperators / $plan->operators_limit) * 100, 1)
            : 0;

        return [
            Stat::make('Current Plan', $plan->name)
                ->description('€'.$plan->price.' / month')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),

            Stat::make('Leads This Month', $leadsThisMonth.' / '.$plan->leads_limit_per_month)
                ->description($leadsPercentage.'% used')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($leadsPercentage >= 90 ? 'danger' : ($leadsPercentage >= 70 ? 'warning' : 'success'))
                ->chart([$leadsThisMonth, $plan->leads_limit_per_month - $leadsThisMonth]),

            Stat::make('Messages Today', $messagesToday.' / '.$plan->messages_limit_per_day)
                ->description($messagesPercentage.'% used')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($messagesPercentage >= 90 ? 'danger' : ($messagesPercentage >= 70 ? 'warning' : 'success'))
                ->chart([$messagesToday, $plan->messages_limit_per_day - $messagesToday]),

            Stat::make('Active Operators', $activeOperators.' / '.$plan->operators_limit)
                ->description($operatorsPercentage.'% capacity')
                ->descriptionIcon('heroicon-m-users')
                ->color($operatorsPercentage >= 100 ? 'danger' : 'success'),

            Stat::make('WhatsApp Instances', $whatsappInstances.' / '.$plan->whatsapp_instances_limit)
                ->description('Connected instances')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color($whatsappInstances >= $plan->whatsapp_instances_limit ? 'warning' : 'success'),
        ];
    }
}
