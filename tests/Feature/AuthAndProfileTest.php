<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(200);
    }

    public function test_user_can_access_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/profile')
            ->assertStatus(200);
    }

    public function test_user_can_update_locale_in_profile(): void
    {
        $user = User::factory()->create(['locale' => 'it']);

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->fillForm([
                'locale' => 'en',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('en', $user->refresh()->locale);
    }
}
