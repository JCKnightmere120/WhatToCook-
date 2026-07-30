<?php

namespace Tests\Unit;

use App\Models\HouseholdProfile;
use App\Services\ChildMealPlanner;
use Carbon\Carbon;
use Tests\TestCase;

class ChildMealPlannerTest extends TestCase
{
    public function test_age_bands_use_birth_date_boundaries_and_not_a_client_age(): void
    {
        $planner = app(ChildMealPlanner::class);
        $date = Carbon::parse('2026-07-30');

        $this->assertSame('0-5_months', $planner->ageBand(Carbon::parse('2026-03-01'), $date));
        $this->assertSame('6-11_months', $planner->ageBand(Carbon::parse('2026-01-30'), $date));
        $this->assertSame('12-23_months', $planner->ageBand(Carbon::parse('2025-07-30'), $date));
        $this->assertSame('2-5_years', $planner->ageBand(Carbon::parse('2024-07-30'), $date));
        $this->assertSame('6_plus_years', $planner->ageBand(Carbon::parse('2020-07-30'), $date));
        $this->assertSame('2-5_years', $planner->ageBand(Carbon::parse('2022-07-30'), $date));
        $this->assertSame('2-5_years', $planner->ageBand(Carbon::parse('2022-08-01'), $date)); // still age 3
    }

    public function test_infants_and_children_have_non_adult_portions_and_adaptation_notes(): void
    {
        $profiles = collect([
            new HouseholdProfile(['id' => 1, 'name' => 'Infant', 'relation' => 'child', 'birth_date' => '2026-04-30']),
            new HouseholdProfile(['id' => 2, 'name' => 'Toddler', 'relation' => 'child', 'birth_date' => '2024-07-30']),
        ]);
        $plan = app(ChildMealPlanner::class)->plan($profiles, Carbon::parse('2026-07-30'));

        $this->assertSame(0.0, $plan['children'][0]['portion_multiplier']);
        $this->assertSame(0.65, $plan['children'][1]['portion_multiplier']);
        $this->assertStringContainsString('salt', strtolower(implode(' ', $plan['children'][1]['adaptation_notes'])));
        $this->assertStringContainsString('not medical', strtolower($plan['medical_disclaimer']));
    }
}
