<?php

namespace Tests\Feature;

use App\Enums\Channel;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Meta\MetaGraphApiMockService;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstagramOAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->app->instance(MetaGraphApiServiceInterface::class, new MetaGraphApiMockService);
    }

    public function test_redirect_sets_state_and_redirects_to_instagram_oauth(): void
    {
        $response = $this->actingAs($this->user)->get('/oauth/instagram/redirect');

        $response->assertRedirect();
        $this->assertNotNull(session('instagram_oauth_state'));

        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://www.instagram.com/oauth/authorize', $location);
    }

    public function test_callback_creates_meta_account_on_success(): void
    {
        $state = 'valid_state_string';
        session(['instagram_oauth_state' => $state]);

        $response = $this->actingAs($this->user)
            ->get('/oauth/instagram/callback?code=test_code&state='.$state);

        $response->assertRedirect();
        $response->assertSessionHas('meta_oauth_success');

        $this->assertDatabaseHas('meta_accounts', [
            'tenant_id' => $this->tenant->id,
            'type' => Channel::Instagram->value,
            'instagram_user_id' => 'mock_ig_user_123',
            'page_name' => 'mock_ig_user',
            'source' => 'instagram_business_login',
            'status' => 'connected',
        ]);
    }

    public function test_callback_does_not_duplicate_account_on_reconnect(): void
    {
        $state = 'valid_state_string';
        session(['instagram_oauth_state' => $state]);
        $this->actingAs($this->user)
            ->get('/oauth/instagram/callback?code=test_code&state='.$state);

        $state = 'valid_state_string_2';
        session(['instagram_oauth_state' => $state]);
        $this->actingAs($this->user)
            ->get('/oauth/instagram/callback?code=test_code&state='.$state);

        $this->assertDatabaseCount('meta_accounts', 1);
    }

    public function test_callback_redirects_with_error_when_denied(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/oauth/instagram/callback?error=access_denied');

        $response->assertRedirect();
        $response->assertSessionHas('meta_oauth_error');
        $this->assertDatabaseCount('meta_accounts', 0);
    }

    public function test_callback_rejects_invalid_state(): void
    {
        session(['instagram_oauth_state' => 'correct_state']);

        $response = $this->actingAs($this->user)
            ->get('/oauth/instagram/callback?code=test_code&state=wrong_state');

        $response->assertRedirect();
        $response->assertSessionHas('meta_oauth_error');
        $this->assertDatabaseCount('meta_accounts', 0);
    }

    public function test_callback_rejects_missing_state(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/oauth/instagram/callback?code=test_code');

        $response->assertRedirect();
        $response->assertSessionHas('meta_oauth_error');
        $this->assertDatabaseCount('meta_accounts', 0);
    }
}
