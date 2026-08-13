# WhatToCook product roadmap

## Delivered through Phase 6

- Separate personal and family pantry, diner, shopping-list, and cooking boundaries.
- Approved ingredient catalog with alias suggestion/confirmation and rejection of unknown non-food input.
- Pantry matching across split lots, weight/volume conversion, and user-confirmed package conversions.
- USDA FoodData Central search/cache flow and server-calculated recipe/meal nutrition with clear incompleteness reporting.
- Birth-date-derived child portions, preparation adaptations, conservative young-child recipe exclusions, and a medical disclaimer.
- Explainable deterministic meal ranking: safety first, then nutrition, pantry/expiry use, time, preferences, and variety.
- Draft/review/save planning, explicit conflict handling, cooking preflight, stock deduction, and immutable meal history.
- Automated backend and frontend coverage plus a production Angular build and a panel-ready manual checklist.

## Product constraints to preserve

- Never merge personal and family inventory or use one to satisfy the other.
- Never bypass allergy, dietary, access-control, or child-safety exclusions with a client request.
- Never treat barcode, voice, receipt, alias, or purchase input as confirmed inventory without user review, quantity, and unit.
- Never infer incompatible package-to-mass/volume conversions; require a scoped user conversion.
- Never present incomplete nutrition as complete, or present child guidance as medical advice.
- Never save a draft, replace a scheduled meal, deduct stock, or alter a completed record without an explicit user action.

## Future work (not Phase 6 scope)

1. Add individually editable dates/attendance for diners beyond every-day, weekday, and weekend patterns.
2. Add confirmed pantry-backed substitutions with full safety revalidation.
3. Expand USDA matching curation and nutrition coverage for common local ingredients.
4. Add budget estimates, reminders, expiry/waste insights, accessibility review, offline support, and mobile end-to-end tests.
5. Add licensed, documented recipe images with a consistent local fallback.
