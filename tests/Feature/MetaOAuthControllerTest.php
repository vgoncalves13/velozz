<?php

namespace Tests\Feature;

use App\Enums\Channel;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Meta\MetaGraphApiMockService;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class MetaOAuthControllerTest extends TestCase
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

    public function test_redirect_returns_redirect_to_facebook(): void
    {
        $provider = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $provider->shouldReceive('scopes')->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(redirect('https://www.facebook.com/v22.0/dialog/oauth'));

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $response = $this->actingAs($this->user)->get('/oauth/meta/redirect');

        $response->assertRedirect();
    }

    public function test_callback_success_creates_facebook_messenger_account(): void
    {
        $socialiteUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->token = 'short_lived_token';

        $provider = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $response = $this->actingAs($this->user)->get('/oauth/meta/callback');

        $response->assertRedirect();

        $this->assertDatabaseHas('meta_accounts', [
            'tenant_id' => $this->tenant->id,
            'type' => Channel::FacebookMessenger->value,
            'page_id' => 'mock_page_1',
            'page_name' => 'Mock Page',
            'status' => 'connected',
        ]);
    }

    public function test_callback_creates_instagram_account_when_page_has_it(): void
    {
        $socialiteUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->token = 'short_lived_token';

        $provider = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $mockWithInstagram = new class extends MetaGraphApiMockService
        {
            public function getInstagramBusinessAccount(string $pageId, string $pageAccessToken): ?string
            {
                return 'ig_business_account_123';
            }
        };

        $this->app->instance(MetaGraphApiServiceInterface::class, $mockWithInstagram);

        $this->actingAs($this->user)->get('/oauth/meta/callback');

        $this->assertDatabaseHas('meta_accounts', [
            'tenant_id' => $this->tenant->id,
            'type' => Channel::Instagram->value,
            'page_id' => 'mock_page_1',
            'instagram_user_id' => 'ig_business_account_123',
            'status' => 'connected',
        ]);
    }

    public function test_callback_handles_denied_permission(): void
    {
        $response = $this->actingAs($this->user)->get('/oauth/meta/callback?error=access_denied&error_reason=user_denied');

        $response->assertRedirect();
        $response->assertSessionHas('meta_oauth_error');
    }

    public function test_callback_does_not_duplicate_accounts_on_reconnect(): void
    {
        $socialiteUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->token = 'short_lived_token';

        $provider = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $provider->shouldReceive('user')->twice()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('facebook')->twice()->andReturn($provider);

        $this->actingAs($this->user)->get('/oauth/meta/callback');
        $this->actingAs($this->user)->get('/oauth/meta/callback');

        $this->assertDatabaseCount('meta_accounts', 1);
    }
}
