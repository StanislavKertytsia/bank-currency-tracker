<?php

namespace Tests\Unit;

use App\DTOs\RateDTO;
use App\Services\Providers\MinfinProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MinfinProviderTest extends TestCase
{
    private MinfinProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new MinfinProvider();
    }

    public function test_get_rates_returns_dto_with_correct_mapping(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    [
                        'organization' => ['slug' => 'privatbank'],
                        'bid' => 39.5,
                        'ask' => 40.1,
                        'date' => '2026-05-01',
                    ],
                ],
            ]),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertCount(1, $rates);
        $this->assertInstanceOf(RateDTO::class, $rates[0]);
        $this->assertEquals(39.5, $rates[0]->buyRate);
        $this->assertEquals(40.1, $rates[0]->sellRate);
        $this->assertEquals('privatbank', $rates[0]->bankSlug);
        $this->assertEquals('minfin', $rates[0]->source);
        $this->assertEquals('USD', $rates[0]->currencyCode);
    }

    public function test_get_rates_skips_items_without_required_fields(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    [
                        'organization' => ['slug' => 'privatbank'],
                        'bid' => 39.5,
                        'ask' => 40.1,
                        'date' => '2026-05-01',
                    ],
                    [
                        // Missing slug
                        'organization' => [],
                        'bid' => 39.5,
                        'ask' => 40.1,
                    ],
                    [
                        // Missing bid
                        'organization' => ['slug' => 'monobank'],
                        'ask' => 40.1,
                    ],
                    [
                        // Missing ask
                        'organization' => ['slug' => 'oschadbank'],
                        'bid' => 39.5,
                    ],
                ],
            ]),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertCount(1, $rates);
        $this->assertEquals('privatbank', $rates[0]->bankSlug);
    }

    public function test_get_rates_uses_fallback_on_http_error(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertNotEmpty($rates);
        $this->assertInstanceOf(RateDTO::class, $rates[0]);
        $this->assertEquals('privatbank', $rates[0]->bankSlug);
    }

    public function test_get_rates_uses_fallback_on_empty_data(): void
    {
        Http::fake([
            '*' => Http::response(['data' => []]),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertNotEmpty($rates);
    }

    public function test_get_rates_uses_fallback_on_connection_exception(): void
    {
        Http::fake([
            '*' => Http::sequence()->push(fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection refused')),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertNotEmpty($rates);
        $this->assertEquals('privatbank', $rates[0]->bankSlug);
    }

    public function test_get_rates_returns_multiple_bank_dtos(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['organization' => ['slug' => 'privatbank'], 'bid' => 39.5, 'ask' => 40.1, 'date' => '2026-05-01'],
                    ['organization' => ['slug' => 'monobank'], 'bid' => 39.6, 'ask' => 40.2, 'date' => '2026-05-01'],
                ],
            ]),
        ]);

        $rates = $this->provider->getRates('USD');

        $this->assertCount(2, $rates);
    }
}
