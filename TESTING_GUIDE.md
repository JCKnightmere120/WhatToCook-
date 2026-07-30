# WhatToCook testing guide

## Automated verification

Run these from the project root. Use the explicit Angular commands; they perform a production type-check and a one-run headless browser test.

```powershell
cd whattocook-backend
php artisan test

cd ..\frontend
npx ng build --configuration production
npx ng test --watch=false --browsers=ChromeHeadless
```

Phase 6 result: backend 62 tests / 380 assertions; frontend 22 ChromeHeadless specs; production build passes.

## Exact project-defense scenarios

1. **Personal-versus-family boundary.** Add personal rice and different family rice. Generate personal and family recommendations. Confirm each uses only its matching pantry; try accessing another family's pantry and confirm denial.
2. **Catalog confirmation.** Add `bihon`: confirm the alias is suggested as the approved canonical ingredient and requires confirmation. Attempt `chair`: confirm it is rejected and no pantry item is created.
3. **Unit conversion and review.** Put 500 g chicken plus 0.5 kg chicken in personal pantry. Match a 1 kg recipe and confirm Ready. Put four packs of bihon against a 250 g requirement and confirm Needs quantity review. Set `1 pack = 250 g`; confirm matching/deduction then works.
4. **USDA nutrition completeness.** Link a 200 g ingredient to a cached per-100 g USDA food and verify recipe totals and per-serving values. Leave a food link or gram conversion missing and verify `is_complete` is false with an explicit unmatched reason.
5. **Child/age-aware plan.** Create a household diner with a birth date in the toddler range and select the diner. Confirm child portion/adaptation notes, then verify a recipe with alcohol or siling labuyo is not auto-selected. Confirm the same rules are determined from birth date on the plan date.
6. **Ranking proof.** Add a peanut allergy and a like for peanut; confirm a peanut recipe is excluded. Add a near-expiry tomato and compare recipes: the tomato recipe ranks higher. Mark it already chosen and confirm the repeat penalty can move another recipe first. Enable strict 15-minute preference and confirm a 45-minute recipe is excluded.
7. **Draft-to-cook audit trail.** Generate a draft with shortages, add shortages to the correct shopping list, save it, then preflight cooking. Confirm deducting is disabled until Ready; after cooking, confirm quantity changes and exactly one history entry. Repeat with “cooked without pantry update” and confirm stock does not change.

## Panel manual checklist

Before the demonstration, run migrations, start Laravel (`php artisan serve`) and Angular (`npm start`), and sign in with a completed profile.

- [ ] In Personal mode, add a catalog-confirmed pantry item and generate a draft; verify it is not on the calendar before Save.
- [ ] Create/select a family, add a dependent diner, and switch back and forth between Personal and Family; verify the pantry and safety rules change with the context.
- [ ] Select a child, generate meals across a weekday and weekend, and open the child guidance for a meal date.
- [ ] Open draft review and show Ready, Low stock, Missing, and Needs quantity review states.
- [ ] Add shortages to shopping, record a purchase with amount and unit, then recheck the draft.
- [ ] Show an allergy-conflicting recipe is absent from search/recommendations.
- [ ] Open nutrition for a linked recipe and identify per-serving values; show the incomplete indicator if using an intentionally unmatched ingredient.
- [ ] Save the plan, open Meal Details, run preflight, cook once with deduction, and verify Meal History.
- [ ] Complete a second meal without pantry update and verify history changes while stock does not.

## Expected outcomes

Family purchases must be added to the selected family pantry, not personal pantry. A draft intentionally stays off the calendar until saved. Missing recipes can be an intended allergy, diet, child-safety, strict-time, or selected-diner exclusion rather than a search failure.
