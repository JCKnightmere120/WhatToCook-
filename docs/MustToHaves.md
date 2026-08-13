WhatToCook

WhatToCook is a web and mobile meal planning application designed to help Filipino households plan meals using ingredients already available in their pantry. The system recommends Filipino recipes based on ingredient availability, quantity, dietary preferences, household member restrictions, spoilage risk, nutrition needs, and serving size. It also suggests alternatives for missing or spoiled ingredients, generates meal plans and shopping lists, and provides step-by-step cooking instructions.

The goal of the application is to make family meal planning more convenient, reduce food waste, and support practical home cooking. The app is focused on Filipino households where ingredients may come from supermarkets, sari-sari stores, wet markets, leftovers, or homegrown sources.

Proponents
Jacob Matthews Villacorte
Jason Conopio
Josh Allen Tipactipac

MUST HAVES

1. System Architecture

Frontend
- Ionic Framework
- Angular
- Capacitor
- Responsive mobile user interface
- Proper routing and navigation
- Android Studio integration for Android build and testing

Backend
- Laravel 13
- REST API
- Laravel Sanctum authentication
- SQLite database for local development and automated tests
- Recipe recommendation module
- Pantry inventory management
- Nutrition data module
- Meal planning module
- Shopping list generator

2. Authentication and Account Management

- User registration
- User login
- User logout
- User profile management
- Secure API access using Laravel Sanctum

3. Household Multi-Profile Management

One account must be able to manage one or more household member profiles. This is needed because meal planning should depend on the people included in the meal, not only on the main account owner.

Each household member profile should include:
- Name
- Sex
- Age
- Height
- Weight
- Activity level
- Health or body goal, such as maintain weight, lose weight, or gain weight
- Allergies
- Dietary restrictions
- Food preferences if needed

Example:
- Dave, male, 22 years old, 58 kg, lean, goal is maintain or gain weight
- Sarah, female, 7 years old, child, with child-appropriate nutrition needs
- John, male, 48 years old, overweight, goal is weight control

The user should be able to include or exclude members when generating a meal plan. For example, if the father is away for the week, the meal plan should only calculate meals for the selected members.

4. Pantry Management

- Add pantry ingredients
- Edit pantry ingredients
- Delete pantry ingredients
- Manual ingredient input
- Voice input
- Receipt scanning or OCR if time permits
- Pantry inventory dashboard
- Ingredient expiration and spoilage tracking
- Ingredient quantity and unit tracking

When adding stock, the user should provide freshness-related details:
- Ingredient name
- Quantity
- Unit
- Purchase source
- Purchase date
- Optional printed expiry date
- Storage type
- Freshness condition if known

Purchase source options should include:
- Supermarket
- Sari-sari store
- Wet market
- Homegrown
- Leftover
- Unknown

Freshness logic:
- If the item is from a supermarket and has a printed expiry date, the system should use the printed expiry date.
- If the item is from a supermarket but has no printed expiry date, the system should estimate freshness based on ingredient type.
- If the item is from a sari-sari store, wet market, homegrown source, or leftover, the system should not pretend to know the exact expiry date. Instead, it should estimate a review date and ask the user to confirm if the item is still fresh.
- For uncertain items, the system should show a freshness review reminder such as Check Tomorrow or Review Freshness.
- The user can mark an item as Still Fresh, Spoiled, Used, or Discarded.
- If the user marks Still Fresh, the system can extend the review date depending on the ingredient type.

This is more realistic than automatically making every sari-sari store item expire in one day, because some items from sari-sari stores are packaged and may last longer.

5. Nutrition Integration

The app must include nutrition information for ingredients, recipes, and meal plans.

The system should use USDA FoodData Central or a similar nutrition data source to get nutrient information. If USDA does not contain a specific Filipino ingredient or local product, the system may use a manually verified local nutrition entry.

Nutrition data should include:
- Calories
- Protein
- Carbohydrates
- Fat
- Fiber if available
- Sodium if available
- Sugar if available

The system should calculate:
- Nutrition per ingredient
- Nutrition per recipe serving
- Total nutrition per recipe
- Nutrition per meal plan day
- Nutrition summary for weekly or two-week meal plans

Nutrition is a must-have because the meal planner depends on household member profiles, body goals, restrictions, and serving sizes.

6. Recipe Recommendation

The app must recommend recipes based on:
- Available pantry ingredients
- Ingredient quantity sufficiency
- Missing ingredients
- Spoilage risk or freshness status
- Dietary restrictions
- Allergies
- Selected household members
- Serving count
- Nutrition goals
- Filipino recipe focus
- Estimated preparation and cooking time
- Previous ratings if available

The recommendation should show:
- Recipe name
- Match percentage
- Available ingredients
- Missing ingredients
- Suggested substitutes
- Estimated cooking time
- Serving size
- Nutrition per serving

7. Weekly and Two-Week Meal Planner

The app must generate meal plans for:
- 1 week
- 2 weeks

Meal planning should allow the user to:
- Select which household members are included
- Exclude members who are not eating during that period
- Adjust servings automatically based on selected members
- Save meal plans
- Edit meal plans
- View daily meal schedule
- Replace a suggested meal

The meal planning algorithm should try to satisfy:
- Pantry availability
- Expiring ingredients first
- Allergies and restrictions
- Nutrition and macro needs
- Number of servings
- Recipe ratings and preferences
- Practical cooking time

8. Grocery List Generator

- Automatically generate shopping lists
- Detect missing ingredients
- Detect insufficient ingredient quantity
- Prevent duplicate grocery purchases
- Mark purchased items
- Connect purchased items back to pantry inventory if possible

9. Cooking Guide

- Step-by-step cooking instructions
- Required ingredients list
- Suggested ingredient substitutions
- Cooking tips if available
- Optional cooking timer
- Mark step as done

10. Meal Ratings and Feedback

Family members should be able to rate meals after cooking.

Ratings should help future recommendations by considering:
- Meals the family liked
- Meals the family disliked
- Member-specific preferences
- Recipes that are repeated too often

This makes recommendations more personalized over time.

11. Main Navigation

- Dashboard
- Pantry
- Recipes
- Meal Planner
- Shopping List
- Cooking Guide
- Household Profiles
- Favorites
- Profile
- Settings


must additions
- Dark mode
- Pantry expiration push notifications
- Barcode scanning
- Advanced receipt scanning
- Local store price comparison
- Family shared pantry across multiple login accounts
- Meal history analytics
- AI-generated recipe variations

DEVELOPMENT TOOLS

Frontend
- Ionic Angular for mobile app
- Capacitor for Android integration
- Android Studio for device testing and release preparation

Backend
- PHP Laravel for API and business logic
- Laravel Sanctum for authentication
- SQLite for database

Important note:
This document may still be updated as the capstone scope becomes clearer. The system should be designed to be scalable, but the team should still build it in phases so the main features are completed first.


updated more 

System
· The system must be available as both a web-based and mobile application.
· The system must support user authentication and profile management.
· The system must support the creation and management of family accounts with individual member profiles.
· The system must use user and family member profiles, including health conditions, allergies, dietary restrictions, likes, and dislikes, as a basis for personalized meal recommendations.
· The system must support a shared pantry for family accounts.
· The system must support multiple methods of pantry ingredient input, including manual input, voice input, and receipt scanning.
· The system must use Optical Character Recognition (OCR) and a Large Language Model (LLM) to extract and identify ingredients from receipts.
· The system must use the United States Department of Agriculture (USDA) FoodData Central or a similar nutritional database to retrieve nutrient information for ingredients.
· The system must track ingredient expiration dates and spoilage information.
· The system must assign a default spoilage date of one day after an ingredient is added when no expiration date is provided, with the date being adjustable by the user.
· The system must recommend recipes based on available ingredients, user profiles, nutritional requirements, family size, dietary preferences, and food preferences.
· The system must support a Filipino recipe database.
· The system must provide ingredient matching and substitution recommendations.
· The system must support both personal and family-based meal planning.
· The system must determine the appropriate number of servings based on the selected family size.
· The system must generate weekly meal plans based on the available pantry ingredients and the profiles of the intended diners.
· The system must provide nutritional information for recipes and meals, including calories, macronutrients, and micronutrients.
· The system must automatically identify missing ingredients from generated meal plans and recipes.
· The system must prevent duplicate grocery purchases by comparing required ingredients with existing pantry inventory.
· The system must support the export of missing ingredients in formats such as image, text, and PDF.
· The system must provide recipe instructions, ingredient lists, cooking tips, preparation times, and cooking times.
· The system must support recipe favorites, ratings, and reviews.
· The system must maintain a history of previously prepared or scheduled meals.
· The system must support dark mode and customizable application themes.
· The system must provide centralized navigation for the Dashboard, Pantry, Recipes, Weekly Meal Planner, Shopping List, Favorites, Profile, and Settings.
User
· The user must register and log in to the system.
· The user must be able to manage their personal profile.
· The user must be able to create or join a family account.
· The user must be able to add and manage family members.
· The user must be assigned a role (e.g., owner or member) when joining or creating a family account.
· The user must be able to control which of their health conditions, dietary restrictions, or preferences are visible to other family members.
· The user must be able to specify the health conditions, allergies, dietary restrictions, likes, and dislikes of themselves and their family members.
· The user must be able to manage a personal or shared family pantry.
· The user must be able to add, edit, and delete pantry ingredients.
· The user must be able to add ingredients manually, through voice input, or by scanning receipts.
· The user must be able to review and confirm ingredients extracted from receipts.
· The user must be able to adjust ingredient spoilage dates when necessary.
· The user must be able to view recommended recipes and meals.
· The user must be able to select whether a meal plan is intended for personal or family use.
· The user must be able to set the desired family size or number of servings.
· The user must be able to generate, save, edit, and view weekly meal plans.
· The user must be able to view daily meal schedules.
· The user must be able to view recipe nutrition information and meal nutritional values.
· The user must be able to view and manage generated shopping lists.
· The user must be able to mark grocery items as purchased.
· The user must be able to export missing ingredients in supported formats.
· The user must be able to save recipes as favorites.
· The user must be able to rate and review recipes.
· The user must be able to view their meal history.
· The user must be able to switch between available application themes, including dark mode.
