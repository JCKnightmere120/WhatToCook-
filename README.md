# WhatToCook

WhatToCook is an Ionic Angular app and Laravel API that helps Filipino households turn pantry items into practical meal plans.

## Stack

- Frontend: Ionic 8 + Angular 20
- Backend: Laravel 13 (PHP 8.3+)
- Database: SQLite (the local development default)
- Authentication: Laravel Sanctum bearer tokens

## Local setup

1. Install PHP 8.3+, Composer, Node.js/npm, and an SQLite-enabled PHP build.
2. In `whattocook-backend`, run `composer setup`. This creates `.env`, creates the SQLite database when needed, runs migrations, and builds Laravel's frontend assets.
3. Start the API with `php artisan serve` from `whattocook-backend`.
4. In `frontend`, run `npm install` and then `npm start`.

The backend uses `database/database.sqlite` by default. It is machine-local and intentionally ignored by Git. Do not commit `.env`, `vendor`, `node_modules`, Ionic build output, or generated `WhatToCook-*.zip` hand-off archives.

## Production handoff

The production API, hosted database, Android release build, rollback procedure, and secret-handling boundaries are documented in [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md). No hosting target, credentials, API key, signing key, or release artifact is committed to this repository.

Android release builds require a real HTTPS API endpoint at build time:

```powershell
cd frontend
$env:WHATTOCOOK_API_BASE_URL = 'https://api.your-domain.example'
npm run build:android
```

The command writes a gitignored generated environment file and synchronizes the Android web bundle. Open `android` in Android Studio to build and sign the release APK/AAB; signing stays in the developer's secure local or CI secret store.

## Verification

Run these from their respective project directories:

```powershell
# whattocook-backend
php artisan test

# frontend (production endpoint is intentionally supplied, never committed)
$env:WHATTOCOOK_API_BASE_URL = 'https://api.example.test'
npm run lint
npm run build
npm test -- --watch=false --browsers=ChromeHeadless
```

See [SETUP_GUIDE.md](SETUP_GUIDE.md), [TESTING_GUIDE.md](TESTING_GUIDE.md), [ERD.md](ERD.md), [CAPSTONE_DEMO_CHECKLIST.md](CAPSTONE_DEMO_CHECKLIST.md), and [the backend guide](whattocook-backend/README.md) for the full delivery package.
