<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_locale_defaults_to_en(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->assertEquals('en', $user->locale);
    }

    public function test_user_locale_can_be_set_to_pt(): void
    {
        $user = User::factory()->create(['locale' => 'pt']);

        $this->assertEquals('pt', $user->locale);
    }

    public function test_user_locale_is_fillable(): void
    {
        $user = User::factory()->create();
        $user->fill(['locale' => 'pt']);
        $user->save();

        $this->assertEquals('pt', $user->fresh()->locale);
    }
}
