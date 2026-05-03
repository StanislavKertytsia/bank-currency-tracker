<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\RateHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_returns_200_with_paginated_response(): void
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();
        $currency = Currency::factory()->create();

        RateHistory::factory()->count(5)->create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_history_filtered_by_bank_id(): void
    {
        $user = User::factory()->create();
        $bank1 = Bank::factory()->create();
        $bank2 = Bank::factory()->create();
        $currency = Currency::factory()->create();

        RateHistory::factory()->create(['bank_id' => $bank1->id, 'currency_id' => $currency->id]);
        RateHistory::factory()->create(['bank_id' => $bank2->id, 'currency_id' => $currency->id]);

        $response = $this->actingAs($user)->getJson("/api/history?bank_id={$bank1->id}");

        $response->assertStatus(200);

        $bankIds = collect($response->json('data'))->pluck('bank.id');
        $this->assertTrue($bankIds->every(fn ($id) => $id === $bank1->id));
    }

    public function test_history_filtered_by_currency_id(): void
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();
        $currency1 = Currency::factory()->create();
        $currency2 = Currency::factory()->create();

        RateHistory::factory()->create(['bank_id' => $bank->id, 'currency_id' => $currency1->id]);

        $bank2 = Bank::factory()->create();
        RateHistory::factory()->create(['bank_id' => $bank2->id, 'currency_id' => $currency2->id]);

        $response = $this->actingAs($user)->getJson("/api/history?currency_id={$currency1->id}");

        $response->assertStatus(200);

        $currencyIds = collect($response->json('data'))->pluck('currency.id');
        $this->assertTrue($currencyIds->every(fn ($id) => $id === $currency1->id));
    }

    public function test_history_filtered_by_date_range(): void
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();
        $currency = Currency::factory()->create();

        RateHistory::factory()->create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'recorded_at' => '2026-05-01 12:00:00',
        ]);

        RateHistory::factory()->create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'recorded_at' => '2026-04-20 12:00:00',
        ]);

        $response = $this->actingAs($user)->getJson('/api/history?from=2026-05-01&to=2026-05-02');

        $response->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_history_returns_422_when_to_is_before_from(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/history?to=2026-01-01&from=2026-05-01');

        $response->assertStatus(422);
    }

    public function test_history_respects_per_page_parameter(): void
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();
        $currency = Currency::factory()->create();

        RateHistory::factory()->count(10)->create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/history?per_page=5');

        $response->assertStatus(200);

        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(5, $response->json('meta.per_page'));
    }
}
