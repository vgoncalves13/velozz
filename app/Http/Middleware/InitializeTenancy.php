<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Check if this is a tenant subdomain
        if (!str_ends_with($host, '.velozz.test') || $host === 'velozz.test') {
            // Not a tenant request, continue normally
            return $next($request);
        }

        // Find tenant by domain
        $tenant = Tenant::where('domain', $host)->first();

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        // Check if tenant is accessible
        if ($tenant->status === 'blocked') {
            abort(403, 'Tenant is blocked. Please contact support.');
        }

        if ($tenant->status === 'suspended') {
            abort(403, 'Tenant is suspended. Please update your subscription.');
        }

        // Store current tenant in app container
        app()->instance('tenant', $tenant);

        // Add global scope to all queries
        if (method_exists(\Illuminate\Database\Eloquent\Model::class, 'addGlobalScope')) {
            \Illuminate\Database\Eloquent\Model::addGlobalScope('tenant', function ($query) use ($tenant) {
                if (in_array('tenant_id', $query->getModel()->getFillable())) {
                    $query->where('tenant_id', $tenant->id);
                }
            });
        }

        return $next($request);
    }
}
