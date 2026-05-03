<?php

namespace Tests\Unit;

use App\DTOs\RateDTO;
use App\Services\Providers\NbuProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NbuProviderTest extends TestCase
{
    private NbuProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new NbuProvider();
    }

    public function test_get_rates_returns_single_dto_filtered_by_currency_code(): void
    {
        Http::fake([
            '*' => Http::response([
                ['cc' => 'USD', 'rate' => 41.2, 'exchangedate' => '01.05.2026'],
                ['cc' => 'EUR', 'rate' => 44.5, 'exchangedate' => '01.05.2026'],
            ]),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertCount(1, $rates);
        $this->assertInstanceOf(RateDTO::class, $rates[0]);
        $this->assertEquals(41.2, $rates[0]->buyRate);
        $this->assertEquals(41.2, $rates[0]->sellRate);
        $this->assertEquals('nbu', $rates[0]->source);
        $this->assertEquals('nbu', $rates[0]->bankSlug);
        $this->assertEquals('USD', $rates[0]->currencyCode);
    }

    public function test_get_rates_filters_by_eur_code(): void
    {
        Http::fake([
            '*' => Http::response([
                ['cc' => 'USD', 'rate' => 41.2, 'exchangedate' => '01.05.2026'],
                ['cc' => 'EUR', 'rate' => 44.5, 'exchangedate' => '01.05.2026'],
            ]),
        ]);

        $rates = $this->provider->getRates('EUR');

        $this->assertCount(1, $rates);
        $this->assertEquals(44.5, $rates[0]->buyRate);
        $this->assertEquals('EUR', $rates[0]->currencyCode);
    }

    public function test_get_rates_returns_empty_array_for_unsupported_currency(): void
    {
        Http::fake([
            '*' => Http::response([]),
        ]);

        $rates = $this->provider->getRates('RUB');

        $this->assertEmpty($rates);
    }

    public function test_get_rates_uses_fallback_on_http_error(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertNotEmpty($rates);
        $this->assertInstanceOf(RateDTO::class, $rates[0]);
        $this->assertEquals('USD', $rates[0]->currencyCode);
        $this->assertEquals('nbu', $rates[0]->bankSlug);
    }

    public function test_get_rates_uses_fallback_on_empty_response(): void
    {
        Http::fake([
            '*' => Http::response([]),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertNotEmpty($rates);
        $this->assertEquals('USD', $rates[0]->currencyCode);
    }

    public function test_get_rates_uses_fallback_on_connection_exception(): void
    {
        Http::fake([
            '*' => Http::sequence()->push(fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection refused')),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertNotEmpty($rates);
        $this->assertEquals('USD', $rates[0]->currencyCode);
    }

    public function test_get_rates_is_case_insensitive_for_currency_code(): void
    {
        Http::fake([
            '*' => Http::response([
                ['cc' => 'USD', 'rate' => 41.2, 'exchangedate' => '01.05.2026'],
            ]),
        ]);

        $rates = $this->provider->getRates('usd');

        $this->assertCount(1, $rates);
        $this->assertEquals('USD', $rates[0]->currencyCode);
    }
}
