<?php

namespace Tests\Feature;

use App\Listeners\LogAuthenticationEvents;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAuditDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_audit_log_is_not_duplicated_within_5_seconds(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $listener = new LogAuthenticationEvents;

        // First login event
        $event = new Login('web', $user, false);
        $listener->handleLogin($event);

        // Second login event (duplicate - within 5 seconds)
        $listener->handleLogin($event);

        // Should only have ONE audit log entry
        $this->assertEquals(1, AuditLog::where('entity', 'user')
            ->where('entity_id', $user->id)
            ->where('action', 'login')
            ->count());
    }

    public function test_login_audit_log_is_created_after_5_seconds(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $listener = new LogAuthenticationEvents;

        // First login event
        $event = new Login('web', $user, false);
        $listener->handleLogin($event);

        // Simulate time passing (6 seconds)
        $this->travel(6)->seconds();

        // Second login event (after timeout)
        $listener->handleLogin($event);

        // Should have TWO audit log entries
        $this->assertEquals(2, AuditLog::where('entity', 'user')
            ->where('entity_id', $user->id)
            ->where('action', 'login')
            ->count());
    }

    public function test_different_users_login_audits_are_independent(): void
    {
        $tenant = Tenant::factory()->create();
        $user1 = User::factory()->create(['tenant_id' => $tenant->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant->id]);

        $listener = new LogAuthenticationEvents;

        // User 1 logs in
        $event1 = new Login('web', $user1, false);
        $listener->handleLogin($event1);

        // User 2 logs in (immediately after)
        $event2 = new Login('web', $user2, false);
        $listener->handleLogin($event2);

        // Each user should have their own audit log entry
        $this->assertEquals(1, AuditLog::where('entity', 'user')
            ->where('entity_id', $user1->id)
            ->where('action', 'login')
            ->count());

        $this->assertEquals(1, AuditLog::where('entity', 'user')
            ->where('entity_id', $user2->id)
            ->where('action', 'login')
            ->count());
    }

    public function test_logout_audit_log_is_not_duplicated_within_5_seconds(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $listener = new LogAuthenticationEvents;

        // First logout event
        $event = new Logout('web', $user);
        $listener->handleLogout($event);

        // Second logout event (duplicate - within 5 seconds)
        $listener->handleLogout($event);

        // Should only have ONE audit log entry
        $this->assertEquals(1, AuditLog::where('entity', 'user')
            ->where('entity_id', $user->id)
            ->where('action', 'logout')
            ->count());
    }

    public function test_logout_audit_log_is_created_after_5_seconds(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $listener = new LogAuthenticationEvents;

        // First logout event
        $event = new Logout('web', $user);
        $listener->handleLogout($event);

        // Simulate time passing (6 seconds)
        $this->travel(6)->seconds();

        // Second logout event (after timeout)
        $listener->handleLogout($event);

        // Should have TWO audit log entries
        $this->assertEquals(2, AuditLog::where('entity', 'user')
            ->where('entity_id', $user->id)
            ->where('action', 'logout')
            ->count());
    }

    public function test_logout_with_null_user_is_ignored(): void
    {
        $listener = new LogAuthenticationEvents;

        // Logout event with null user
        $event = new Logout('web', null);
        $listener->handleLogout($event);

        // Should have NO audit log entries
        $this->assertEquals(0, AuditLog::where('action', 'logout')->count());
    }
}
