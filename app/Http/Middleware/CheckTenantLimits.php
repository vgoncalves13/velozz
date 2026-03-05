<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantLimits
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->status === 'suspended' || $tenant->status === 'blocked') {
            abort(403, 'Account suspended. Please contact support.');
        }

        if ($tenant->status === 'trial' && $tenant->trial_ends_at?->isPast()) {
            $tenant->update(['status' => 'suspended']);
            abort(403, 'Trial expired. Please activate a subscription.');
        }

        return $next($request);
    }
}
