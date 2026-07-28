# WhatToCook implementation plan

## Product direction

WhatToCook is a family-first Filipino meal-planning system, not a solo recipe browser. The active household and the people selected to eat must drive the pantry view, recommendations, serving count, meal plan, nutrition totals, and shopping list.

The Ionic application is the shared mobile and web client. Laravel is the REST API and business-logic layer; it does not need to be the user-facing web UI unless the team intentionally chooses to build a second client.

## Delivery order

| Phase | Ionic client | Laravel/API responsibility | Done when |
| --- | --- | --- | --- |
| 1. Foundation | Sign up, sign in, persistent session, logout, onboarding, protected routes | Sanctum, user/profile endpoints, migrations | A new user can create an account and reach a protected dashboard without pasting a token. |
| 2. Family core | Household switcher; create/join household; family/member screens; privacy controls | Household role/invite API; dependent member profiles; authorization | An owner can add a child/dependent or invite a registered member and edit their nutrition-relevant profile. |
| 3. Family dashboard and pantry | Dashboard like the approved reference; shared/personal pantry; status/edit/delete; expiry review | Shared-data scoping; pantry source/storage/freshness fields | The dashboard highlights household pantry status, expiring items, and family-safe recommendations. |
| 4. Phone input | Manual, barcode, voice, receipt camera upload, confirmation/review screen | Barcode/product lookup; receipt upload; OCR; LLM ingredient normalization | No scanned or spoken ingredient enters stock until the user confirms it. |
| 5. Recipes and nutrition | Recipe detail, substitutions, nutrition display, favorites/ratings | Filipino recipe data; USDA caching/linking; recipe nutrition calculation | Nutrition comes from a backend USDA/local-food record, not a frontend API key. |
| 6. Family planning | Personal/family selector; diner chips; weekly/two-week plan; replace/save | Family-aware scoring, servings, weekly generator, meal totals | A plan changes when the selected diners or their restrictions change. |
| 7. Grocery, history, polish | Deduplicated list, purchased state, image/text/PDF export, history, settings/themes | Aggregated deficits, shared ownership, exports/history APIs, notifications | The complete required workflow works on web and Android. |

## Required family data model

Keep account membership separate from people who eat meals:

- `families`: household and owner.
- `family_memberships`: registered users, roles, invitation/join state.
- `household_profiles`: every diner, including children/dependents without a login; an optional linked user account; name, relationship, sex, birth date/age, height, weight, activity, goal, health conditions, allergies, restrictions, likes, dislikes, and visibility rules.

The current `FamilyMember` model only supports a registered user added by email. It cannot represent a child or dependent, so this new profile layer is required before family-aware planning is considered complete.

## Integration boundaries

- **USDA FoodData Central:** Laravel stores the API key, searches/caches foods, records FDC IDs/source, and returns normalized nutrients. Ionic never receives the secret key.
- **Receipt OCR + LLM:** Ionic captures/uploads a receipt. Laravel runs OCR, sends only receipt text to the chosen LLM for structured ingredient candidates, and returns a reviewable draft. The user confirms before pantry insertion.
- **Voice:** Ionic captures speech on the phone and sends recognized text to the same review flow.
- **Barcode:** Ionic scans on the phone; Laravel/product lookup resolves the code where possible, with manual editing always available.

## UI structure

Use five primary mobile tabs: **Dashboard**, **Pantry**, **Plan**, **Recipes**, and **More**. Put Shopping List, Family & Members, Favorites, History, Profile, and Settings under More. Every main screen includes the active context: **Personal** or the selected **Family**.

The default light theme will follow the supplied compact dashboard direction. Dark mode remains a selectable theme, not an accidental default caused by the operating system.
