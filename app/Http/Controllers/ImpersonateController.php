<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImpersonateController extends Controller
{
    /** Generate a one-time impersonation token and redirect to the tenant panel. */
    public function generate(Request $request, string $tenantDomain): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->isAdminMaster(), 403);

        $token = Str::random(64);

        Cache::put("impersonate:{$token}", $user->id, now()->addSeconds(60));

        return redirect()->away("http://{$tenantDomain}/app/impersonate?token={$token}");
    }

    /** Called from within the client panel to switch to another tenant (admin_master only). */
    public function switchTenant(Request $request, string $tenantSlug): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->isAdminMaster(), 403);

        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $token = Str::random(64);

        Cache::put("impersonate:{$token}", $user->id, now()->addSeconds(60));

        return redirect()->away("http://{$tenant->domain}/app/impersonate?token={$token}");
    }

    /** Consume the one-time token and authenticate the admin_master in this panel. */
    public function consume(Request $request): RedirectResponse
    {
        $token = $request->query('token');

        abort_unless($token, 403);

        $cacheKey = "impersonate:{$token}";
        $userId = Cache::pull($cacheKey); // pull = get + delete (one-time use)

        abort_unless($userId, 403, 'Impersonation token invalid or expired.');

        $user = User::find($userId);

        abort_unless($user?->isAdminMaster(), 403);

        Auth::login($user, remember: false);

        return redirect('/app');
    }
}
