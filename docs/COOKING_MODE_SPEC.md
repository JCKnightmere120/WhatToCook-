# Guided cooking mode specification

## Purpose and entry point

Guided cooking is the authenticated Ionic workflow for completing one scheduled meal without bypassing pantry and meal-history rules. The route is `/cooking/:id`; it is protected by `AuthGuard` and is opened from an unfinished meal's details screen. An invalid meal-plan ID or missing signed-in user returns the user to `/tabs/meal-plan`.

## Session loading

On entry, the client requests `GET /meal-plans/{id}/preflight`. The response supplies the scheduled meal, recipe, diners, active pantry scope, ingredient readiness, and whether pantry deduction is allowed. If the meal is already complete, the app redirects to `/meal-details/{id}` instead of opening a second cooking session.

The page displays the recipe name, total preparation plus cooking time, servings, newline-separated instructions, optional cooking tip, and an ingredient checklist. Recipe instruction progress is stored only on the device and is keyed by signed-in user and meal-plan ID; it is restored when the user resumes the session and cleared after successful completion. Checklist selections and the optional timer are session-local.

## Interaction behavior

1. Users move through the parsed instruction steps with Back and Next. The page shows the current step and a percentage/progress label.
2. The timer accepts one to 180 minutes, can be started, paused, or reset, and stops when it reaches zero or the page is left.
3. The ingredient checklist is a preparation aid. It does not alter the preflight readiness calculation or authorize stock deduction.
4. Users may add up to 5,000 characters of optional cooking notes before completion.

## Completion paths

| User choice | API request | Result |
| --- | --- | --- |
| **Cook & deduct pantry** | `POST /meal-plans/{id}/complete` with `{ "notes": string|null }` | Available only when `can_cook_from_pantry` is true. After confirmation, the server completes the meal, deducts compatible active-context pantry stock, and records meal history. |
| **Mark cooked without pantry update** | `POST /meal-plans/{id}/complete-without-deduction` with `{ "notes": string|null }` | After confirmation, the server records completion and meal history without changing pantry stock. |

Both paths redirect to `/meal-details/{id}` on success, display the API success message when supplied, and clear the device-local step progress. Errors remain on the cooking page with the API message when available. While a completion request is in progress, completion and step-navigation actions are disabled.

## Boundaries to preserve

- The backend, not the client checklist, determines pantry readiness and deduction eligibility.
- The active personal or family pantry scope comes from the scheduled meal's preflight response; private and family stock must not be combined.
- Completion is explicit and confirmed. The client must not deduct stock, create history, or replace the server completion flow locally.
- A completed meal is not reopened through guided cooking; it is viewed through meal details and history.

## Implementation references

- Route: `frontend/src/app/app-routing.module.ts`
- Page and UI: `frontend/src/app/cooking/cooking.page.ts` and `cooking.page.html`
- Device-local progress: `frontend/src/app/services/cooking-progress.service.ts`
- Client API methods: `frontend/src/app/services/api.service.ts`
- Laravel routes: `whattocook-backend/routes/api.php`
