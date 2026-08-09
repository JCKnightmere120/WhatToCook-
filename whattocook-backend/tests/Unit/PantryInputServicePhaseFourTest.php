<?php

namespace Tests\Unit;

use App\Models\IngredientCatalog;
use App\Services\PantryInputService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PantryInputServicePhaseFourTest extends TestCase
{
    use RefreshDatabase;

    public function test_filipino_aliases_are_suggestions_and_duplicate_voice_candidates_are_consolidated_for_review(): void
    {
        IngredientCatalog::updateOrCreate(['canonical_name' => 'eggs'], ['aliases' => ['itlog'], 'category' => 'dairy', 'default_units' => ['pieces'], 'is_approved' => true]);
        $service = app(PantryInputService::class);

        $result = $service->fromVoice('isang piraso itlog at 2 pieces itlog');

        $this->assertTrue($result['needs_review']);
        $this->assertCount(1, $result['suggested']);
        $this->assertSame('eggs', $result['suggested'][0]['suggestion']['canonical_name']);
        $this->assertSame('3', $result['suggested'][0]['quantity']);
        $this->assertSame('pieces', $result['suggested'][0]['unit']);
    }

    public function test_receipt_prices_and_totals_do_not_become_pantry_candidates(): void
    {
        IngredientCatalog::updateOrCreate(['canonical_name' => 'rice'], ['aliases' => ['bigas'], 'category' => 'grains', 'default_units' => ['kg'], 'is_approved' => true]);
        $result = app(PantryInputService::class)->fromText("1 kg bigas 55.00\nTOTAL 55.00", 'receipt');

        $this->assertCount(1, $result['suggested']);
        $this->assertSame('1', $result['suggested'][0]['quantity']);
        $this->assertSame('kg', $result['suggested'][0]['unit']);
        $this->assertSame('Bigas', $result['suggested'][0]['name']);
    }
}
