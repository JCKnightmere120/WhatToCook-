import { Component } from '@angular/core';
import { ApiService, Family, PantryItem, Recommendation } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';
import { forkJoin, of } from 'rxjs';
import { catchError, switchMap } from 'rxjs/operators';

@Component({ selector: 'app-dashboard', templateUrl: 'dashboard.page.html', styleUrls: ['dashboard.page.scss'], standalone: false })
export class DashboardPage {
  personalItems: PantryItem[] = [];
  householdItems: PantryItem[] = [];
  families: Family[] = [];
  activeFamily?: Family;
  recommendations: Recommendation[] = [];
  loading = false;
  error = '';

  constructor(private api: ApiService, private householdContext: HouseholdContextService, public auth: AuthService) {}
  ionViewWillEnter() { this.loadDashboardData(); }
  loadDashboardData(): void {
    const userId = this.auth.user?.id;
    if (!this.api.hasToken || !userId) return;
    this.loading = true; this.error = '';
    this.householdContext.refresh(userId).pipe(
      switchMap(context => {
        this.families = context.families;
        this.activeFamily = context.activeFamily || undefined;
        const familyId = this.activeFamily?.id;
        return forkJoin({
          personalItems: this.api.pantry(undefined, true).pipe(catchError(() => of([] as PantryItem[]))),
          householdItems: familyId
            ? this.api.pantry(familyId).pipe(catchError(() => of([] as PantryItem[])))
            : of([] as PantryItem[]),
          recommendations: this.api.recommendations(familyId).pipe(catchError(() => of({ recommendations: [] as Recommendation[] }))),
        });
      }),
    ).subscribe({
      next: ({ personalItems, householdItems, recommendations }) => {
        this.personalItems = personalItems;
        this.householdItems = this.activeFamily
          ? householdItems.filter(item => item.family_id === this.activeFamily?.id)
          : [];
        this.recommendations = recommendations.recommendations.slice(0, 3);
        this.loading = false;
      },
      error: () => {
        this.personalItems = [];
        this.householdItems = [];
        this.recommendations = [];
        this.error = 'Unable to load your household. Please refresh and try again.';
        this.loading = false;
      },
    });
  }

  attention(items: PantryItem[]): PantryItem[] { const limit = Date.now() + 3 * 24 * 60 * 60 * 1000; return items.filter(item => item.freshness_status === 'review' || (item.expiry_date && new Date(item.expiry_date).getTime() <= limit)).slice(0, 4); }
}
