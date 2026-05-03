<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_list_returns_200_with_banks(): void
    {
        Bank::factory()->count(3)->create();

        $response = $this->getJson('/api/banks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);
    }

    public function test_bank_list_excludes_nbu_bank(): void
    {
        Bank::factory()->create(['slug' => 'nbu', 'name' => 'НБУ']);
        Bank::factory()->create(['slug' => 'privatbank', 'name' => 'PrivatBank']);

        $response = $this->getJson('/api/banks');

        $response->assertStatus(200);

        $slugs = collect($response->json('data'))->pluck('slug');
        $this->assertNotContains('nbu', $slugs->toArray());
        $this->assertContains('privatbank', $slugs->toArray());
    }

    public function test_bank_detail_returns_200_with_correct_structure(): void
    {
        $bank = Bank::factory()->create();
        $currency = Currency::factory()->create();

        ExchangeRate::factory()->create([
            'bank_id' => $bank->id,
            'currency_id' => $currency->id,
        ]);

        Branch::factory()->create(['bank_id' => $bank->id]);

        $response = $this->getJson("/api/banks/{$bank->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'rates',
                    'branches',
                ],
            ]);
    }

    public function test_bank_detail_returns_404_for_nonexistent_bank(): void
    {
        $response = $this->getJson('/api/banks/99999');

        $response->assertStatus(404);
    }
}
