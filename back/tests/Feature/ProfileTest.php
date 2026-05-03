<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'notification_enabled'],
            ])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_name(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_update_profile_fails_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_toggle_notifications(): void
    {
        $user = User::factory()->create(['notification_enabled' => false]);

        $response = $this->actingAs($user)->putJson('/api/profile/notifications', [
            'notification_enabled' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['notification_enabled']])
            ->assertJsonPath('data.notification_enabled', true);

        $this->assertTrue((bool) $user->fresh()->notification_enabled);
    }

    public function test_toggle_notifications_fails_without_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile/notifications', []);

        $response->assertStatus(422);
    }
}
