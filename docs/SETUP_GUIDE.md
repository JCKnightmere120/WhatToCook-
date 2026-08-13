# WhatToCook setup guide

## Local development

1. Install PHP 8.3+, Composer, Node.js 22+, npm, and Android Studio only if building Android.
2. In `whattocook-backend`, run `composer setup`, then `php artisan serve`.
3. In `frontend`, run `npm install`, then `npm start`.
4. Use `http://127.0.0.1:8000/api` in a desktop browser. Android emulator development uses `http://10.0.2.2:8000/api`; a physical device needs a temporary LAN endpoint and should not be used for a release.

Local `.env`, database files, build output, Android signing files, and generated production API configuration remain untracked.

## Release preparation

1. Provision the HTTPS API hostname and hosted MySQL/PostgreSQL database.
2. Configure server secrets and `CORS_ALLOWED_ORIGINS` as described in [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md).
3. Run the deployment smoke test and database backup confirmation.
4. Set `WHATTOCOOK_API_BASE_URL` to the HTTPS API hostname, run `npm run build:android`, then build/sign in Android Studio.
5. Install the signed release on a physical device and complete the Android checklist in [TESTING_GUIDE.md](TESTING_GUIDE.md).
