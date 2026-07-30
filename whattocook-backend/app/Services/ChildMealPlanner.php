<?php

namespace App\Services;

use App\Models\HouseholdProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Planning aid only: it makes age-appropriate portions and preparation notes
 * visible to parents. It deliberately does not give medical feeding advice.
 */
class ChildMealPlanner
{
    public function ageBand(?Carbon $birthDate, Carbon $onDate): ?string
    {
        if (! $birthDate || $birthDate->gt($onDate)) return null;

        $months = $birthDate->copy()->startOfDay()->diffInMonths($onDate->copy()->startOfDay());
        return match (true) {
            $months <= 5 => '0-5_months',
            $months <= 11 => '6-11_months',
            $months <= 23 => '12-23_months',
            $months < 72 => '2-5_years',
            default => '6_plus_years',
        };
    }

    public function plan(Collection $profiles, Carbon $onDate, array $modes = []): array
    {
        $children = $profiles->map(function (HouseholdProfile $profile) use ($onDate, $modes) {
            $band = $this->ageBand($profile->birth_date, $onDate);
            if (! $band || ! $this->isChild($profile, $band)) return null;
            $mode = $modes[$profile->id] ?? 'family_meal_with_adaptation';
            $details = $this->detailsFor($band);

            return [
                'profile_id' => $profile->id,
                'name' => $profile->name,
                'age_band' => $band,
                'meal_choice' => $mode,
                'portion_multiplier' => $mode === 'exclude' ? 0.0 : $details['portion_multiplier'],
                'portion' => $details['portion'],
                'adaptation_notes' => $mode === 'exclude' ? ['Excluded from this meal.'] : $details['notes'],
                'guidance_note' => 'Use your child\'s established feeding plan and contact a pediatric professional for individual guidance.',
            ];
        })->filter()->values();

        return [
            'children' => $children->all(),
            'serving_equivalents' => round($profiles->sum(function (HouseholdProfile $profile) use ($children) {
                $child = $children->firstWhere('profile_id', $profile->id);
                return $child ? $child['portion_multiplier'] : 1.0;
            }), 2),
            'medical_disclaimer' => 'This is meal-planning support, not medical or pediatric guidance.',
        ];
    }

    private function isChild(HouseholdProfile $profile, string $band): bool
    {
        return $band !== '6_plus_years' || in_array(strtolower((string) $profile->relation), ['child', 'daughter', 'son', 'toddler', 'infant'], true);
    }

    private function detailsFor(string $band): array
    {
        return match ($band) {
            '0-5_months' => ['portion_multiplier' => 0.0, 'portion' => 'No family-meal serving', 'notes' => ['Do not count as an adult serving.', 'Follow the child\'s established feeding plan; do not introduce foods based on this planner.']],
            '6-11_months' => ['portion_multiplier' => 0.25, 'portion' => 'Small infant portion (about 1/4 serving)', 'notes' => ['Set aside an unsalted, unspiced portion before seasoning.', 'Offer only a texture already appropriate for the child.']],
            '12-23_months' => ['portion_multiplier' => 0.5, 'portion' => 'Toddler portion (about 1/2 serving)', 'notes' => ['Set aside before adding salt or spicy ingredients.', 'Cut, mash, or soften to the child\'s familiar texture.']],
            '2-5_years' => ['portion_multiplier' => 0.65, 'portion' => 'Child portion (about 2/3 serving)', 'notes' => ['Keep seasoning mild and add salt/spice to adult plates later where practical.', 'Adapt the texture and cut food to the child\'s usual needs.']],
            default => ['portion_multiplier' => 0.75, 'portion' => 'Child portion (about 3/4 serving)', 'notes' => ['Serve a child-sized portion and adjust seasoning to the child\'s preferences.']],
        };
    }
}
