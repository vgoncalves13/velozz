<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->isBlocked()) {
            abort(403, 'Tenant is blocked. Please contact support.');
        }

        if ($tenant->isSuspended()) {
            abort(403, 'Tenant is suspended. Please update your subscription.');
        }

        app()->instance('tenant', $tenant);

        // For admin_master (no tenant_id), temporarily assign the current tenant
        // in-memory so all tenant_id checks work correctly for this request.
        // syncOriginalAttribute() prevents Eloquent from marking it as dirty,
        // so no accidental DB persist happens if save() is called downstream.
        $user = auth()->user();
        if ($user && $user->isAdminMaster()) {
            $user->tenant_id = $tenant->id;
            $user->syncOriginalAttribute('tenant_id');
        }

        Model::addGlobalScope('tenant', function ($query) use ($tenant) {
            if (in_array('tenant_id', $query->getModel()->getFillable())) {
                $query->where('tenant_id', $tenant->id);
            }
        });

        return $next($request);
    }
}
