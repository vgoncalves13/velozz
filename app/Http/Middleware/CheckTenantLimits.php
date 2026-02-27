<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantLimits
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->tenant_id) {
            return $next($request);
        }

        $tenant = Tenant::find($user->tenant_id);

        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->status === 'suspended' || $tenant->status === 'blocked') {
            abort(403, 'Account suspended. Please contact support.');
        }

        if ($tenant->status === 'trial' && $tenant->trial_ends_at && $tenant->trial_ends_at < now()) {
            $tenant->update(['status' => 'suspended']);
            abort(403, 'Trial expired. Please activate a subscription.');
        }

        return $next($request);
    }
}
