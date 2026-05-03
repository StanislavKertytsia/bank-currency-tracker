<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateTest extends TestCase
{
    use RefreshDatabase;

    public function test_rates_endpoint_returns_200(): void
    {
        $bank = Bank::factory()->create();
        $currency = Currency::factory()->create();

        ExchangeRate::factory()->create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'source' => 'minfin',
        ]);

        $response = $this->getJson('/api/rates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'buy_rate', 'sell_rate', 'source'],
                ],
            ]);
    }

    public function test_rates_endpoint_excludes_nbu_source(): void
    {
        $nbuBank = Bank::factory()->create(['slug' => 'nbu']);
        $currency = Currency::factory()->create();

        ExchangeRate::factory()->create([
            'bank_id' => $nbuBank->id,
            'currency_id' => $currency->id,
            'source' => 'nbu',
        ]);

        $mfinBank = Bank::factory()->create();
        ExchangeRate::factory()->create([
            'bank_id' => $mfinBank->id,
            'currency_id' => $currency->id,
            'source' => 'minfin',
        ]);

        $response = $this->getJson('/api/rates');

        $response->assertStatus(200);

        $sources = collect($response->json('data'))->pluck('source');
        $this->assertNotContains('nbu', $sources->toArray());
    }

    public function test_rates_filtered_by_bank_id(): void
    {
        $bank1 = Bank::factory()->create();
        $bank2 = Bank::factory()->create();
        $currency = Currency::factory()->create();

        ExchangeRate::factory()->create([
            'bank_id' => $bank1->id,
            'currency_id' => $currency->id,
            'source' => 'minfin',
        ]);

        ExchangeRate::factory()->create([
            'bank_id' => $bank2->id,
            'currency_id' => $currency->id,
            'source' => 'minfin',
        ]);

        $response = $this->getJson("/api/rates?bank_id={$bank1->id}");

        $response->assertStatus(200);

        $bankIds = collect($response->json('data'))->pluck('bank.id');
        $this->assertTrue($bankIds->every(fn ($id) => $id === $bank1->id));
    }

    public function test_rates_filtered_by_currency_id(): void
    {
        $bank = Bank::factory()->create();
        $currency1 = Currency::factory()->create();
        $currency2 = Currency::factory()->create();

        ExchangeRate::factory()->create([
            'bank_id' => $bank->id,
            'currency_id' => $currency1->id,
            'source' => 'minfin',
        ]);

        $bank2 = Bank::factory()->create();
        ExchangeRate::factory()->create([
            'bank_id' => $bank2->id,
            'currency_id' => $currency2->id,
            'source' => 'minfin',
        ]);

        $response = $this->getJson("/api/rates?currency_id={$currency1->id}");

        $response->assertStatus(200);

        $currencyIds = collect($response->json('data'))->pluck('currency.id');
        $this->assertTrue($currencyIds->every(fn ($id) => $id === $currency1->id));
    }

    public function test_rates_returns_422_for_nonexistent_bank_id(): void
    {
        $response = $this->getJson('/api/rates?bank_id=99999');

        $response->assertStatus(422);
    }

    public function test_nbu_rates_returns_200_with_correct_structure(): void
    {
        $response = $this->getJson('/api/rates/nbu');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'nbu',
                    'averages',
                ],
            ]);
    }
}
