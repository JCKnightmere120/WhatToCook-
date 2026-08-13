# WhatToCook ERD

The visual ERD is retained as [capstone erd.jpg](../capstone%20erd.jpg). This source-level ERD records the Phase 6 deliverable schema and is easier to review alongside migrations.

```mermaid
erDiagram
  USERS ||--o| PROFILES : has
  USERS ||--o{ FAMILIES : owns
  FAMILIES ||--o{ FAMILY_MEMBERS : includes
  USERS ||--o{ FAMILY_MEMBERS : joins
  FAMILIES ||--o{ HOUSEHOLD_PROFILES : contains
  USERS o|--o{ HOUSEHOLD_PROFILES : represents
  USERS ||--o{ PANTRY_ITEMS : owns_personal
  FAMILIES o|--o{ PANTRY_ITEMS : owns_shared
  RECIPES ||--o{ INGREDIENTS : contains
  RECIPES ||--o{ MEAL_PLANS : schedules
  FAMILIES o|--o{ MEAL_PLANS : scopes
  MEAL_PLAN_BATCHES ||--o{ MEAL_PLANS : drafts
  USERS ||--o{ SHOPPING_LISTS : owns_personal
  FAMILIES o|--o{ SHOPPING_LISTS : owns_shared
  RECIPES ||--o{ RECIPE_FAVORITES : favorited
  RECIPES ||--o{ RECIPE_REVIEWS : reviewed
  RECIPES ||--o{ MEAL_HISTORY : cooked
  INGREDIENT_CATALOG ||--o{ INGREDIENT_PACKAGE_CONVERSIONS : converts
  NUTRITION_FOODS o|--o{ INGREDIENTS : nutritional_match
```

Important boundaries: a family membership is a registered account relationship, while a household profile represents any diner, including a child without a login. Pantry, plans, and shopping records carry either personal ownership or family scope; API authorization enforces that boundary.
