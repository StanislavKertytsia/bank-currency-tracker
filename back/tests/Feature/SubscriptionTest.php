<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_subscriptions(): void
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();

        Subscription::factory()->create(['user_id' => $user->id, 'bank_id' => $bank->id]);

        $response = $this->actingAs($user)->getJson('/api/subscriptions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id'],
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_list_subscriptions(): void
    {
        $response = $this->getJson('/api/subscriptions');

        $response->assertStatus(401);
    }

    public function test_user_can_subscribe_to_bank(): void
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/subscriptions', [
            'bank_id' => $bank->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'bank_id' => $bank->id,
        ]);
    }

    public function test_user_can_subscribe_to_currency(): void
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/subscriptions', [
            'currency_id' => $currency->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'currency_id' => $currency->id,
        ]);
    }

    public function test_subscription_requires_bank_or_currency(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/subscriptions', []);

        $response->assertStatus(422);
    }

    public function test_duplicate_subscription_returns_409(): void
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();

        Subscription::factory()->create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'currency_id' => null,
        ]);

        $response = $this->actingAs($user)->postJson('/api/subscriptions', [
            'bank_id' => $bank->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_user_can_delete_own_subscription(): void
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/subscriptions/{$subscription->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }

    public function test_user_cannot_delete_other_users_subscription(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $bank = Bank::factory()->create();

        $subscription = Subscription::factory()->create([
            'user_id' => $owner->id,
            'bank_id' => $bank->id,
        ]);

        $response = $this->actingAs($other)->deleteJson("/api/subscriptions/{$subscription->id}");

        $response->assertStatus(403);
    }
}
