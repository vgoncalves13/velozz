<?php

namespace App\Listeners;

use App\Helpers\AuditHelper;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationEvents
{
    public function handleLogin(Login $event): void
    {
        AuditHelper::log('login', 'user', $event->user->id, null, [
            'guard' => $event->guard,
            'remember' => $event->remember,
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            AuditHelper::log('logout', 'user', $event->user->id, null, [
                'guard' => $event->guard,
            ]);
        }
    }

    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
        ];
    }
}
