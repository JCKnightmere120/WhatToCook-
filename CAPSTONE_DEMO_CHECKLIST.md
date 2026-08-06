# Capstone demonstration checklist

## Before the panel

- [ ] CI status is green; run the commands in [TESTING_GUIDE.md](TESTING_GUIDE.md).
- [ ] Use a fresh demonstration account and a known seeded recipe set.
- [ ] Confirm the API `/up` health endpoint and the selected browser/Android HTTPS endpoint.
- [ ] Prepare one personal pantry and one family pantry with different rice or protein quantities.
- [ ] Verify no production secrets, real user data, or Android signing material appear in slides, screen recordings, or the repository.

## Demonstration flow

1. Register/sign in and complete the health/profile preferences.
2. Create or select a family; add a child/dependent profile and show Personal versus Family scope.
3. Add pantry items through the reviewed input flow; show a freshness state.
4. Search/recommend recipes and show allergy, preference, and pantry-match effects.
5. Generate a draft plan for selected diners; show child guidance, shortages, and quantity-review states.
6. Add shortages to shopping, confirm a purchase, recheck readiness, and save the plan.
7. Open preflight, cook once with pantry deduction, and show meal history; also show the no-deduction alternative.
8. On the signed Android build, sign in and repeat a protected read plus one pantry action over HTTPS.

## Evidence to retain

- [ ] Screenshot/video of Android release API connectivity and expected error handling.
- [ ] Test/CI output, deployment health response, and backup/restore confirmation.
- [ ] Current [ERD](ERD.md), architecture/deployment plan, setup guide, and test guide.
