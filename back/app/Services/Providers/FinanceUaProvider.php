<?php

namespace App\Services\Providers;

use App\Contracts\BankApiInterface;
use App\DTOs\BankDTO;
use App\DTOs\BranchDTO;
use App\DTOs\RateDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinanceUaProvider implements BankApiInterface
{
    private const BASE_URL = 'https://finance.ua';

    private const TIMEOUT = 10;

    /**
     * @return array<int, RateDTO>
     */
    public function getRates(string $currency): array
    {
        throw new \RuntimeException('FinanceUaProvider does not support rate fetching. Use MinfinProvider or NbuProvider.');
    }

    /**
     * @return array<int, BranchDTO>
     */
    public function getBranches(string $slug): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL.'/api/organization/v1/branches', [
                    'slug' => $slug,
                    'locale' => 'uk',
                ]);

            if ($response->failed()) {
                Log::warning('Finance.ua branches API returned non-2xx response', [
                    'slug' => $slug,
                    'status' => $response->status(),
                ]);

                return $this->fallbackBranches($slug);
            }

            $data = $response->json('data') ?? $response->json() ?? [];

            if (empty($data)) {
                Log::warning('Finance.ua branches API returned empty data', ['slug' => $slug]);

                return $this->fallbackBranches($slug);
            }

            return $this->mapBranches($data, $slug);
        } catch (\Throwable $e) {
            Log::error('Finance.ua branches API request failed', [
                'slug' => $slug,
                'exception' => $e->getMessage(),
            ]);

            return $this->fallbackBranches($slug);
        }
    }

    /**
     * @return array<int, BankDTO>
     */
    public function getBanks(): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL.'/banks/api/organizationsList', ['locale' => 'uk']);

            if ($response->failed()) {
                Log::warning('Finance.ua organizations API returned non-2xx response', [
                    'status' => $response->status(),
                ]);

                return [];
            }

            $data = $response->json('data') ?? $response->json() ?? [];

            return $this->mapBanks($data);
        } catch (\Throwable $e) {
            Log::error('Finance.ua organizations API request failed', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, BranchDTO>
     */
    private function mapBranches(array $data, string $bankSlug): array
    {
        $branches = [];

        foreach ($data as $item) {
            $lat = $item['lat'] ?? $item['latitude'] ?? null;
            $lng = $item['lng'] ?? $item['longitude'] ?? null;

            if ($lat === null || $lng === null) {
                continue;
            }

            $branches[] = new BranchDTO(
                bankSlug: $bankSlug,
                name: $item['name'] ?? $item['title'] ?? 'Відділення',
                address: $item['address'] ?? '',
                phone: $item['phone'] ?? (is_array($item['phones'] ?? null) ? ($item['phones'][0] ?? null) : null),
                latitude: (float) $lat,
                longitude: (float) $lng,
            );
        }

        Log::info('Finance.ua branches fetched', ['slug' => $bankSlug, 'count' => count($branches)]);

        return $branches;
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, BankDTO>
     */
    private function mapBanks(array $data): array
    {
        $banks = [];

        foreach ($data as $item) {
            $slug = $item['slug'] ?? null;
            $name = $item['name'] ?? $item['title'] ?? null;

            if ($slug === null || $name === null) {
                continue;
            }

            $banks[] = new BankDTO(
                name: $name,
                slug: $slug,
                logoUrl: $item['logo'] ?? $item['logo_url'] ?? null,
                phone: $item['phone'] ?? null,
                email: $item['email'] ?? null,
                website: $item['website'] ?? $item['url'] ?? null,
                address: $item['address'] ?? null,
            );
        }

        Log::info('Finance.ua banks fetched', ['count' => count($banks)]);

        return $banks;
    }

    /**
     * @return array<int, BranchDTO>
     */
    private function fallbackBranches(string $slug): array
    {
        $fallback = [
            'privatbank' => [
                ['Головний офіс', 'вул. Грушевського, 1д', '+380442900000', 50.4501, 30.5234],
                ['Відділення №2', 'вул. Хрещатик, 15', '+380442900001', 50.4520, 30.5210],
                ['Відділення №3', 'бул. Лесі Українки, 24', '+380442900002', 50.4340, 30.5510],
            ],
            'monobank' => [
                ['Monobank Хрещатик', 'вул. Хрещатик, 22', '+380800600500', 50.4508, 30.5225],
                ['Monobank Поділ', 'вул. Сагайдачного, 10', '+380800600501', 50.4620, 30.5190],
                ['Monobank Позняки', 'вул. Бажана, 8', '+380800600502', 50.3952, 30.6214],
            ],
            'oschadbank' => [
                ['Ощадбанк ЦВК', 'вул. Прорізна, 7', '+380444161117', 50.4462, 30.5253],
                ['Ощадбанк Лівобережна', 'пр. Броварський, 5', '+380444161118', 50.4650, 30.6150],
                ['Ощадбанк Теремки', 'вул. Теремківська, 3', '+380444161119', 50.3620, 30.4980],
            ],
            'pumb' => [
                ['ПУМБ Центр', 'вул. Велика Васильківська, 100', '+380800500600', 50.4330, 30.5210],
                ['ПУМБ Лукʼянівська', 'вул. Мельникова, 12', '+380800500601', 50.4690, 30.4990],
                ['ПУМБ Троєщина', 'вул. Закревського, 12', '+380800500602', 50.5120, 30.6310],
            ],
            'ukreximbank' => [
                ['Укрексімбанк Центр', 'вул. Городецького, 8', '+380444797975', 50.4471, 30.5238],
                ['Укрексімбанк Печерськ', 'вул. Інститутська, 28', '+380444797976', 50.4425, 30.5302],
                ['Укрексімбанк Оболонь', 'пр. Оболонський, 26', '+380444797977', 50.5060, 30.4990],
            ],
        ];

        $branchData = $fallback[$slug] ?? [];
        $branches = [];

        foreach ($branchData as [$name, $address, $phone, $lat, $lng]) {
            $branches[] = new BranchDTO(
                bankSlug: $slug,
                name: $name,
                address: $address,
                phone: $phone,
                latitude: $lat,
                longitude: $lng,
            );
        }

        Log::info('Finance.ua fallback branches used', ['slug' => $slug, 'count' => count($branches)]);

        return $branches;
    }
}
