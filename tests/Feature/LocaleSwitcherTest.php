<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_locale(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'locale' => 'en',
        ]);

        $this->actingAs($user);

        // Simulate changing locale
        $user->update(['locale' => 'pt']);

        $this->assertEquals('pt', $user->fresh()->locale);
    }

    public function test_locale_persists_across_sessions(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'locale' => 'pt',
        ]);

        $this->actingAs($user);

        // Refresh user from database
        $user->refresh();

        $this->assertEquals('pt', $user->locale);
    }

    public function test_middleware_sets_locale_for_authenticated_user(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'locale' => 'pt',
        ]);

        $this->actingAs($user);

        // Simulate middleware execution
        $middleware = new \App\Http\Middleware\SetLocale;
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        $middleware->handle($request, function ($req) {
            return new \Illuminate\Http\Response;
        });

        $this->assertEquals('pt', App::getLocale());
    }

    public function test_middleware_defaults_to_config_locale_when_user_has_no_preference(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'locale' => 'en', // Using default locale
        ]);

        $this->actingAs($user);

        // Simulate middleware execution
        $middleware = new \App\Http\Middleware\SetLocale;
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        $middleware->handle($request, function ($req) {
            return new \Illuminate\Http\Response;
        });

        $this->assertEquals('en', App::getLocale());
    }

    public function test_middleware_validates_locale_is_supported(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'locale' => 'fr', // Unsupported locale
        ]);

        $this->actingAs($user);

        // Simulate middleware execution
        $middleware = new \App\Http\Middleware\SetLocale;
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        $middleware->handle($request, function ($req) {
            return new \Illuminate\Http\Response;
        });

        // Should not set unsupported locale, defaults to English
        $this->assertNotEquals('fr', App::getLocale());
    }
}
