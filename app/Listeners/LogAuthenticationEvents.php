<?php

namespace App\Listeners;

use App\Helpers\AuditHelper;
use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationEvents
{
    public function handleLogin(Login $event): void
    {
        // Prevent duplicate login logs from multiple Filament panels
        // Check if a login was already logged in the last 5 seconds for this user
        $recentLogin = AuditLog::where('entity', 'user')
            ->where('entity_id', $event->user->id)
            ->where('action', 'login')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentLogin) {
            return;
        }

        // Update last login timestamp
        $event->user->update([
            'last_login_at' => now(),
        ]);

        AuditHelper::log('login', 'user', $event->user->id, null, [
            'guard' => $event->guard,
            'remember' => $event->remember,
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        // Prevent duplicate logout logs from multiple Filament panels
        // Check if a logout was already logged in the last 5 seconds for this user
        $recentLogout = AuditLog::where('entity', 'user')
            ->where('entity_id', $event->user->id)
            ->where('action', 'logout')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentLogout) {
            return;
        }

        AuditHelper::log('logout', 'user', $event->user->id, null, [
            'guard' => $event->guard,
        ]);
    }

    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
        ];
    }
}
