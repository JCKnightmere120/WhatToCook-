# Curated Filipino recipe catalogue

`RecipeSeeder` is the single reviewed manifest for the built-in Filipino recipe
library. To add a recipe, add one complete entry to its `$recipes` array:

1. Use a stable, distinct recipe name and complete method, servings, and ingredient quantities.
2. Use a clear Philippines region and one of the existing meal types.
3. Use pantry-facing ingredient names. `IngredientCatalogSeeder` synchronizes
   those reviewed names into the approved pantry catalogue when `db:seed` runs.
4. Run `php artisan migrate:fresh --seed` locally, then run the feature tests.

The seeder uses `updateOrCreate` and replaces each seeded recipe's ingredient
rows. It is therefore reproducible: rerunning it corrects catalogue metadata
and ingredients instead of retaining stale first-run data. It deliberately does
not delete recipes absent from the manifest, so administrator-created records
and meal-plan references remain safe.
