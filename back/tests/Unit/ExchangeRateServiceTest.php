<?php

namespace Tests\Unit;

use App\DTOs\RateDTO;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\RateHistory;
use App\Services\ExchangeRateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExchangeRateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExchangeRateService();
    }

    private function makeDto(string $bankSlug, string $currencyCode, float $buyRate, float $sellRate, string $source = 'minfin'): RateDTO
    {
        return new RateDTO(
            bankSlug: $bankSlug,
            currencyCode: $currencyCode,
            buyRate: $buyRate,
            sellRate: $sellRate,
            source: $source,
            updatedAt: Carbon::now(),
        );
    }

    public function test_returns_null_when_bank_not_found(): void
    {
        Currency::create(['code' => 'USD', 'name' => 'Dollar']);

        $dto = $this->makeDto('nonexistent-bank', 'USD', 40.0, 41.0);
        $result = $this->service->upsertRate($dto);

        $this->assertNull($result);
    }

    public function test_returns_null_when_currency_not_found(): void
    {
        Bank::create([
            'name' => 'Test Bank',
            'slug' => 'test-bank',
            'description' => null,
            'logo_url' => null,
            'website' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
            'rating' => 3.0,
        ]);

        $dto = $this->makeDto('test-bank', 'XYZ', 40.0, 41.0);
        $result = $this->service->upsertRate($dto);

        $this->assertNull($result);
    }

    public function test_creates_exchange_rate_and_returns_null_when_no_existing_rate(): void
    {
        $bank = Bank::create([
            'name' => 'PrivatBank',
            'slug' => 'privatbank',
            'description' => null,
            'logo_url' => null,
            'website' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
            'rating' => 4.0,
        ]);

        $currency = Currency::create(['code' => 'USD', 'name' => 'Dollar']);

        $dto = $this->makeDto('privatbank', 'USD', 40.0, 41.0);
        $result = $this->service->upsertRate($dto);

        $this->assertNull($result);
        $this->assertDatabaseHas('exchange_rates', [
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'source' => 'minfin',
        ]);
    }

    public function test_updates_rate_and_returns_null_when_change_is_less_than_5_percent(): void
    {
        $bank = Bank::create([
            'name' => 'PrivatBank',
            'slug' => 'privatbank',
            'description' => null,
            'logo_url' => null,
            'website' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
            'rating' => 4.0,
        ]);

        $currency = Currency::create(['code' => 'USD', 'name' => 'Dollar']);

        ExchangeRate::create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'buy_rate' => 40.0,
            'sell_rate' => 41.0,
            'source' => 'minfin',
        ]);

        // 40.0 -> 41.5 = 3.75% change — below threshold
        $dto = $this->makeDto('privatbank', 'USD', 41.5, 42.5);
        $result = $this->service->upsertRate($dto);

        $this->assertNull($result);
        $this->assertDatabaseMissing('rate_histories', [
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
        ]);
    }

    public function test_creates_rate_history_and_returns_it_when_change_is_5_percent_or_more(): void
    {
        $bank = Bank::create([
            'name' => 'PrivatBank',
            'slug' => 'privatbank',
            'description' => null,
            'logo_url' => null,
            'website' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
            'rating' => 4.0,
        ]);

        $currency = Currency::create(['code' => 'USD', 'name' => 'Dollar']);

        ExchangeRate::create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'buy_rate' => 40.0,
            'sell_rate' => 41.0,
            'source' => 'minfin',
        ]);

        // 40.0 -> 43.0 = 7.5% change — above threshold
        $dto = $this->makeDto('privatbank', 'USD', 43.0, 44.0);
        $result = $this->service->upsertRate($dto);

        $this->assertInstanceOf(RateHistory::class, $result);
        $this->assertDatabaseHas('rate_histories', [
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
        ]);
    }

    public function test_rate_history_contains_correct_values(): void
    {
        $bank = Bank::create([
            'name' => 'PrivatBank',
            'slug' => 'privatbank',
            'description' => null,
            'logo_url' => null,
            'website' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
            'rating' => 4.0,
        ]);

        $currency = Currency::create(['code' => 'USD', 'name' => 'Dollar']);

        ExchangeRate::create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'buy_rate' => 40.0,
            'sell_rate' => 41.0,
            'source' => 'minfin',
        ]);

        $dto = $this->makeDto('privatbank', 'USD', 43.0, 44.0);
        $result = $this->service->upsertRate($dto);

        $this->assertInstanceOf(RateHistory::class, $result);
        $this->assertEquals(43.0, (float) $result->buy_rate);
        $this->assertEquals(44.0, (float) $result->sell_rate);
        $this->assertEquals(40.0, (float) $result->previous_buy_rate);
        $this->assertEquals(41.0, (float) $result->previous_sell_rate);
        $this->assertEquals(7.5, (float) $result->change_percent);
    }

    public function test_creates_nbu_bank_automatically_for_nbu_source(): void
    {
        Currency::create(['code' => 'USD', 'name' => 'Dollar']);

        $dto = $this->makeDto('nbu', 'USD', 41.2, 41.2, 'nbu');
        $result = $this->service->upsertRate($dto);

        $this->assertNull($result);
        $this->assertDatabaseHas('banks', ['slug' => 'nbu', 'name' => 'НБУ']);
        $this->assertDatabaseHas('exchange_rates', ['source' => 'nbu']);
    }

    public function test_does_not_create_nbu_bank_twice(): void
    {
        Currency::create(['code' => 'USD', 'name' => 'Dollar']);

        $dto = $this->makeDto('nbu', 'USD', 41.2, 41.2, 'nbu');
        $this->service->upsertRate($dto);
        $this->service->upsertRate(new RateDTO(
            bankSlug: 'nbu',
            currencyCode: 'USD',
            buyRate: 41.5,
            sellRate: 41.5,
            source: 'nbu',
            updatedAt: Carbon::now(),
        ));

        $this->assertEquals(1, Bank::where('slug', 'nbu')->count());
    }

    public function test_updates_existing_exchange_rate_with_new_values(): void
    {
        $bank = Bank::create([
            'name' => 'PrivatBank',
            'slug' => 'privatbank',
            'description' => null,
            'logo_url' => null,
            'website' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
            'rating' => 4.0,
        ]);

        $currency = Currency::create(['code' => 'USD', 'name' => 'Dollar']);

        ExchangeRate::create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'buy_rate' => 40.0,
            'sell_rate' => 41.0,
            'source' => 'minfin',
        ]);

        // Small change — no history but rate should update
        $dto = $this->makeDto('privatbank', 'USD', 40.5, 41.5);
        $this->service->upsertRate($dto);

        $updated = ExchangeRate::where([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
            'source' => 'minfin',
        ])->first();

        $this->assertNotNull($updated);
        $this->assertEquals(40.5, (float) $updated->buy_rate);
        $this->assertEquals(41.5, (float) $updated->sell_rate);
    }
}
