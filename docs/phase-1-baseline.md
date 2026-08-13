# Phase 1 — Dependency/database baseline

Date checked: 2026-08-13

## Reconciliation

- Restored only the Phase 1 dependency changes from `stash@{0}`. Food-safety and privacy/access changes remain in the stash for their respective phases.
- Frontend Angular runtime packages and compiler tooling are pinned at `20.3.27`. Angular CLI/build tooling remain at the compatible `20.3.33` patch level.
- Backend Guzzle requirement is `^7.15.2`; the lockfile resolves it to `7.15.2`.
- The duplicate `2026_08_05_003942_add_is_admin_to_users_table` migration remains removed. The canonical `2026_08_05_000022_add_is_admin_to_users_table` migration is present and recorded as run.

## Database status

`php artisan migrate:status --no-interaction` reports all 31 project migrations as **Ran**. `php artisan migrate --pretend --no-interaction` reports **Nothing to migrate**.

## Dependency audits

| Check | Result |
| --- | --- |
| `composer audit --locked` | Pass: no security vulnerability advisories. |
| `npm audit --package-lock-only --audit-level=high` | 6 findings: 3 high and 3 moderate. The high findings are in `image-size`, reached through `less` and `@angular-devkit/build-angular`; moderate findings are in `uuid`, reached through `sockjs`/`webpack-dev-server`. npm's only automatic remediation upgrades `@angular-devkit/build-angular` to 22.1.3 (a breaking Angular-major upgrade), so it was not applied to the Angular 20.3.27 baseline. |

## Validation

- `composer validate --strict`: manifest valid; Composer warns that an exact Guzzle pin should be avoided. The requirement has therefore been expressed as `^7.15.2` while the lockfile stays at 7.15.2.
- `npm run lint`: passed.
- `npx ng build --configuration development`: passed.
- `npm run build`: intentionally stops before compilation when `WHATTOCOOK_API_BASE_URL` is unset; it requires an HTTPS URL ending in `/api`. No production URL was invented for validation.
