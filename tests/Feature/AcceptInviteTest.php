<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AcceptInviteTest extends TestCase
{
    use RefreshDatabase;

    private function makeInvitedUser(array $overrides = []): User
    {
        $tenant = Tenant::factory()->create();

        return User::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'role' => 'operator',
            'status' => 'invited',
            'invite_token' => Str::random(64),
            'invite_expires_at' => now()->addHours(48),
            'password' => bcrypt(Str::random(32)),
        ], $overrides));
    }

    public function test_invite_form_is_shown_for_valid_token(): void
    {
        $user = $this->makeInvitedUser();

        $this->get("/accept-invite/{$user->invite_token}")
            ->assertOk()
            ->assertViewIs('auth.accept-invite');
    }

    public function test_invite_form_returns_404_for_expired_token(): void
    {
        $user = $this->makeInvitedUser([
            'invite_expires_at' => now()->subHour(),
        ]);

        $this->get("/accept-invite/{$user->invite_token}")
            ->assertNotFound();
    }

    public function test_invite_form_returns_404_for_invalid_token(): void
    {
        $this->get('/accept-invite/invalid-token-xyz')
            ->assertNotFound();
    }

    public function test_accepting_invite_sets_password_and_logs_in(): void
    {
        $user = $this->makeInvitedUser();
        $token = $user->invite_token;

        $this->post("/accept-invite/{$token}", [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect();

        $user->refresh();
        $this->assertEquals('active', $user->status);
        $this->assertNull($user->invite_token);
        $this->assertNull($user->invite_expires_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_accepting_invite_redirects_to_client_panel_with_tenant(): void
    {
        $user = $this->makeInvitedUser(['role' => 'operator']);
        $token = $user->invite_token;

        $response = $this->post("/accept-invite/{$token}", [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirectContains($user->tenant->slug);
    }

    public function test_accepting_invite_fails_with_mismatched_passwords(): void
    {
        $user = $this->makeInvitedUser();

        $this->post("/accept-invite/{$user->invite_token}", [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('password');

        $this->assertEquals('invited', $user->fresh()->status);
    }
}
