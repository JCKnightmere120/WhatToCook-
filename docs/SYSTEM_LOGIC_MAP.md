# WhatToCook system logic map

## Scope and ownership

WhatToCook plans Filipino meals from the pantry in the active context. A user may plan personally without a family, or for a family household. These are deliberately separate boundaries:

| Active context | Profiles used for safety | Pantry used | Shopping list / cooking update |
| --- | --- | --- | --- |
| Personal | Signed-in user's profile | Items with that user's `user_id` and no `family_id` | Personal list and personal pantry only |
| Family | Selected household diner profiles | Items for that exact `family_id` | Family list and shared family pantry only |

An accepted account member receives a linked household diner profile. Dependent children can be diner profiles without accounts. The API checks membership before allowing a family context; it never blends a member's private stock with family stock.

## Pantry and ingredient intake

Pantry entries can be manual or editable candidates produced from barcode, voice/text, or receipt input. Nothing inferred from an input source is saved automatically.

`IngredientCatalogService` normalizes whitespace and case, then considers approved catalog records only:

1. Exact canonical name: accepted.
2. Catalog alias: suggested; the user must confirm the proposed canonical ingredient.
3. No match: rejected and not persisted as a pantry ingredient.

This prevents non-food receipt lines and accidental object names from becoming pantry stock while keeping alias handling transparent.

## Matching, units, and deduction

`RecipeMatcher` matches compatible ingredient names across split pantry lots, sums them in the recipe unit, and reports Ready, Low stock, Missing, or Needs quantity review.

- Weight converts within `g` and `kg`; volume converts within `mL` and `L`.
- Fractions such as `1/2` are parsed for recipe quantities.
- Incompatible dimensions are never guessed. For example, packs cannot satisfy grams until the user creates a scoped package conversion such as `1 pack = 250 g`.
- Package conversions belong to the same personal or family scope as the pantry item.
- Cooking uses the same matcher and reverse conversion as preflight, deducting from compatible lots only after an explicit **Cook & deduct pantry** confirmation.

## Nutrition flow

The Nutrition controller can search USDA FoodData Central and cache an approved food record by FDC ID. `UsdaFoodDataService` fetches through the configured API key, normalizes nutrients to per-100 g values (calories, protein, carbs, fat, fiber, sodium, sugar), stores the raw source payload, and timestamps it.

Each recipe ingredient links to one cached nutrition food and optionally has an explicit gram equivalent. `RecipeNutritionService` converts g/kg/mg/oz/lb quantities to grams, calculates server-side totals and per-serving values, and marks results incomplete when a food link or a gram conversion is missing. The UI must present those unmatched ingredients rather than imply a complete total.

## Age-aware planning

`ChildMealPlanner` derives a child age band from the stored birth date on the meal date, never from a client-supplied age. It returns non-adult serving equivalents and adaptation notes: 0-5 months are excluded from family-meal servings; 6-11 months use 1/4 serving; 12-23 months 1/2; and ages 2-5 use 0.65. Notes recommend setting aside an appropriate texture before salt/spice. This is planning support with a medical disclaimer, not feeding advice.

During ranking, recipes containing alcohol or `siling labuyo` are conservatively excluded when a young child is selected. Allergy and dietary exclusions apply first for every selected diner.

## Generation, review, and cooking

```text
Select personal/family context and diners
  -> exclude unsafe recipes
  -> match only the active pantry
  -> deterministic rank
  -> create draft meals
  -> review shortages, purchases, swaps, and conflicts
  -> explicitly save scheduled meals
  -> preflight when cooking
  -> deduct stock OR record cooked-without-pantry-update
```

The ranker is deterministic and exposes `why_chosen`. It scores nutrition, matched pantry ingredients, soon-to-expire ingredients, fit to time preference, likes, dislikes, and repeat penalties. Safety exclusion is always before scoring; strict time preference removes over-budget recipes. Ties are resolved by recipe ID, so the order is reproducible.

Drafts do not appear as scheduled calendar meals until saved. Replacing a conflicting scheduled meal requires an explicit choice; completed meals remain immutable. Cooking creates one history record and is either stock-deducting (only when all required stock is Ready) or an explicit no-deduction completion.
