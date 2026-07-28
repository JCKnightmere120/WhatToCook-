# WhatToCook Functionality Details

This document explains the important must-have features before implementation. The purpose is to make the project easier to defend and easier to divide between frontend and backend work.

## 1. Project Division

The project has two main folders:

- `frontend` - Ionic Angular mobile app
- `whattocook-backend` - Laravel backend API

The frontend is responsible for the user interface and mobile experience. The backend is responsible for authentication, database records, business logic, recommendation logic, nutrition calculations, and API responses.

## 2. Freshness and Expiry Logic

The app should not simply guess that an ingredient is expired. Instead, it should estimate freshness using the available information.

When adding pantry stock, the user should enter:

- Ingredient name
- Quantity
- Unit
- Purchase source
- Purchase date
- Optional printed expiry date
- Storage type
- Freshness condition if known

Purchase source matters because not all ingredients have the same freshness certainty.

### Supermarket Items

If the item has a printed expiry date, the system should use that date. If there is no printed expiry date, the system can estimate the expiry based on the ingredient type.

### Sari-Sari Store Items

The system should not automatically mark all sari-sari store items as expired after one day. Some sari-sari store items are packaged and may last longer, while some unpacked or fresh items may need checking sooner.

Recommended logic:

- Packaged item with expiry date: use printed expiry date
- Packaged item without expiry date: estimate based on item type and set low confidence
- Fresh or unpacked item: set a short freshness review date
- Unknown freshness: ask user to review tomorrow

### Freshness Review

Instead of using the word retrigger, use:

- Freshness Review
- Still Fresh
- Extend Freshness

Example:

1. User adds tomatoes from a sari-sari store.
2. The system estimates that the item should be checked tomorrow.
3. Tomorrow, the app shows: Check tomatoes.
4. User chooses Still Fresh, Spoiled, Used, or Discarded.
5. If Still Fresh is selected, the system extends the review date.

Defense explanation:

The system uses freshness review because not every ingredient has a reliable expiry date. This makes the app more realistic for Filipino households that buy from supermarkets, sari-sari stores, wet markets, and local sources.

## 3. Nutrition Integration

Nutrition is a must-have because the app recommends meals based on household member profiles and health needs.

The backend should integrate with USDA FoodData Central or a similar nutrition source. The backend should be the one calling the nutrition API because API keys and nutrition logic should not be exposed directly in the mobile app.

Recommended backend flow:

1. User adds an ingredient.
2. Backend checks if the ingredient already exists in the local ingredient database.
3. If not found, backend searches USDA FoodData Central or another source.
4. Backend saves the matched nutrition data locally.
5. Recipe nutrition is calculated from ingredient quantity and serving count.
6. Meal plan nutrition is calculated by adding the nutrition values of planned meals.

Nutrition values to store:

- Calories
- Protein
- Carbohydrates
- Fat
- Fiber if available
- Sodium if available
- Sugar if available

Important note:

USDA may not have every Filipino ingredient, local brand, or local dish. For those cases, the team can create a manually verified local ingredient entry. This is acceptable as long as the source or reason is documented.

Defense explanation:

USDA FoodData Central provides structured nutrition data, but the system also supports manually verified local entries because Filipino ingredients and local brands may not always match international datasets.

## 4. Household Profiles and Meal Planning

One account can manage multiple household members. This is important because a meal plan for one person is different from a meal plan for a family.

Each profile should include:

- Name
- Sex
- Age
- Height
- Weight
- Activity level
- Goal
- Allergies
- Dietary restrictions

The meal planner should let the user choose which members are included for the plan period. For example, if the father is away, the user can exclude him and the system will calculate fewer servings.

The system should consider:

- Number of selected members
- Allergies
- Dietary restrictions
- Body or nutrition goals
- Pantry availability
- Spoilage risk
- Recipe ratings

## 5. Recommendation Logic

The recommendation score can be based on several factors:

- Pantry ingredient match
- Quantity sufficiency
- Missing ingredients
- Spoilage risk
- Dietary restriction compatibility
- Nutrition fit
- Cooking time
- Previous ratings

A simple scoring approach is better for the capstone than a complicated AI model because it is easier to explain, test, and defend.

Example scoring factors:

- Higher score if most ingredients are already available
- Higher score if the recipe uses ingredients that are close to spoilage
- Lower score if many ingredients are missing
- Lower score if the recipe conflicts with a member restriction
- Higher score if the family rated the meal well before

## 6. Suggested Implementation Phases

Phase 1:

- Authentication
- Household profiles
- Pantry CRUD
- Freshness review fields

Phase 2:

- Recipe database
- Recipe ingredient matching
- Missing ingredient detection
- Substitute suggestions
- Grocery list generation

Phase 3:

- Weekly and two-week meal planner
- Member selection
- Serving adjustment

Phase 4:

- Nutrition API integration
- Macro calculation per recipe
- Nutrition summary per meal plan

Phase 5:

- Meal ratings
- Recommendation score improvement
- Android testing and polish

## 7. Frontend and Backend Responsibilities

Frontend should handle:

- Screens
- Forms
- User actions
- Mobile navigation
- Displaying recipe, pantry, and nutrition information
- Android behavior through Capacitor

Backend should handle:

- Authentication
- Database storage
- Pantry logic
- Freshness estimation
- Nutrition API integration
- Recipe matching
- Meal plan generation
- Grocery list generation
- Ratings logic

This keeps the mobile app simpler and keeps important business logic in one place.
