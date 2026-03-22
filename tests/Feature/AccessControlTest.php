<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_users_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(200);
    }

    public function test_non_existing_admin_route_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/non-existing-page')
            ->assertStatus(404);
    }
}
