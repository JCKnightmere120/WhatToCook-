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

    public function test_receipt_text_uses_a_receipt_review_message_and_is_never_persisted(): void
    {
        IngredientCatalog::updateOrCreate(['canonical_name' => 'eggs'], ['aliases' => [], 'category' => 'dairy', 'default_units' => ['pieces'], 'is_approved' => true]);

        $result = app(PantryInputService::class)->fromText('12 eggs', 'receipt');

        $this->assertTrue($result['needs_review']);
        $this->assertStringContainsString('Receipt text', $result['message']);
        $this->assertDatabaseCount('pantry_items', 0);
    }

    public function test_voice_parser_handles_plural_ingredients_and_boxed_items(): void
    {
        IngredientCatalog::updateOrCreate(['canonical_name' => 'eggs'], ['aliases' => [], 'category' => 'dairy', 'default_units' => ['pieces'], 'is_approved' => true]);
        IngredientCatalog::updateOrCreate(['canonical_name' => 'onion'], ['aliases' => [], 'category' => 'vegetables', 'default_units' => ['pieces'], 'is_approved' => true]);
        IngredientCatalog::updateOrCreate(['canonical_name' => 'milk'], ['aliases' => [], 'category' => 'dairy', 'default_units' => ['boxes'], 'is_approved' => true]);
        IngredientCatalog::updateOrCreate(['canonical_name' => 'cheese'], ['aliases' => [], 'category' => 'dairy', 'default_units' => ['packs'], 'is_approved' => true]);

        $result = app(PantryInputService::class)->fromVoice('I have 2 eggs and 2 onions and 2 boxes of milk and 1 cheese');

        $egg = collect($result['candidates'])->first(fn (array $item) => strtolower($item['name']) === 'eggs');
        $onion = collect([...$result['accepted'], ...$result['suggested']])->first(fn (array $item) => strtolower($item['input'] ?? $item['name']) === 'onions');
        $milk = collect([...$result['accepted'], ...$result['suggested']])->first(fn (array $item) => strtolower($item['name']) === 'milk');

        $this->assertSame('2', $egg['quantity']);
        $this->assertNotNull($onion);
        $this->assertSame('2', $onion['quantity']);
        $this->assertNotNull($milk);
        $this->assertSame('2', $milk['quantity']);
        $this->assertSame('boxes', $milk['unit']);
        $cheese = collect($result['candidates'])->first(fn (array $item) => strtolower($item['name']) === 'cheese');
        $this->assertSame('1', $cheese['quantity']);
        $this->assertEmpty($result['rejected']);
    }

    public function test_voice_parser_uses_each_quantity_as_a_boundary_when_speech_omits_and(): void
    {
        foreach ([['eggs', ['egg']], ['onion', ['onions']], ['cabbage', []], ['cheese', []]] as [$name, $aliases]) {
            IngredientCatalog::updateOrCreate(['canonical_name' => $name], ['aliases' => $aliases, 'category' => 'test', 'default_units' => ['pieces'], 'is_approved' => true]);
        }

        $result = app(PantryInputService::class)->fromVoice('I have two eggs two onions two cabbage 2 cheese');

        $this->assertCount(4, $result['candidates']);
        $this->assertSame(['Eggs', 'Onions', 'Cabbage', 'Cheese'], collect($result['candidates'])->pluck('name')->all());
        $this->assertSame('onion', $result['suggested'][0]['suggestion']['canonical_name']);
        $this->assertEmpty($result['rejected']);
    }
}
