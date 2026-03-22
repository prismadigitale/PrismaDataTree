<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_sets_locale_from_user_preference(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->get('/admin');

        $this->assertEquals('en', app()->getLocale());
    }

    public function test_middleware_falls_back_to_app_locale_if_not_set(): void
    {
        $user = User::factory()->create(['locale' => null]);

        $this->actingAs($user)
            ->get('/admin');

        $this->assertEquals(config('app.locale'), app()->getLocale());
    }
}
