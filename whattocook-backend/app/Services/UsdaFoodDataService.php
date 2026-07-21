<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UsdaFoodDataService
{
    public function search(string $query, int $pageSize = 10): array
    {
        return $this->client()->get('/foods/search', [
            'query' => $query,
            'pageSize' => $pageSize,
        ])->throw()->json();
    }

    public function food(int $fdcId): array
    {
        return $this->client()->get("/food/{$fdcId}")->throw()->json();
    }

    private function client()
    {
        $key = config('services.usda.key');
        abort_unless(filled($key), 503, 'USDA nutrition service is not configured.');

        return Http::baseUrl(config('services.usda.base_url'))
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 200)
            ->withQueryParameters(['api_key' => $key]);
    }
}
