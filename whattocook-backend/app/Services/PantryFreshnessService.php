<?php

namespace App\Services;

class PantryFreshnessService
{
    public function estimate(string $name, ?string $unit, ?string $storage): array
    {
        $text = strtolower($name.' '.$unit);
        if (preg_match('/\b(can|canned|tuna|sardines|corned|evaporated|condensed)\b/', $text)) {
            return ['expiry_date' => now()->addMonths(12)->toDateString(), 'review_date' => now()->addMonths(10)->toDateString(), 'status' => 'fresh', 'confidence' => 'low'];
        }
        if (preg_match('/\b(rice|pasta|noodles|flour|sugar|salt|coffee|tea)\b/', $text)) {
            return ['expiry_date' => now()->addMonths(6)->toDateString(), 'review_date' => now()->addMonths(5)->toDateString(), 'status' => 'fresh', 'confidence' => 'low'];
        }
        return ['expiry_date' => now()->addDay()->toDateString(), 'review_date' => now()->addDay()->toDateString(), 'status' => 'review', 'confidence' => $storage === 'refrigerated' ? 'medium' : 'low'];
    }
}
