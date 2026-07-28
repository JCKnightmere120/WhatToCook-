import { Component } from '@angular/core';
import { ApiService, Family, PantryItem, Recommendation } from '../services/api.service';
import { AuthService } from '../services/auth.service';

@Component({ selector: 'app-dashboard', templateUrl: 'dashboard.page.html', styleUrls: ['dashboard.page.scss'], standalone: false })
export class DashboardPage {
  personalItems: PantryItem[] = [];
  householdItems: PantryItem[] = [];
  families: Family[] = [];
  activeFamily?: Family;
  recommendations: Recommendation[] = [];
  loading = false;
  error = '';

  constructor(private api: ApiService, public auth: AuthService) {}
  ionViewWillEnter() { this.loadDashboardData(); }
  loadDashboardData() {
    if (!this.api.hasToken) return;
    this.loading = true; this.error = '';
    this.api.families().subscribe({ next: families => {
      this.families = families;
      const savedId = Number(localStorage.getItem('whattocook_active_family'));
      this.activeFamily = families.find(family => family.id === savedId);
      const familyId = this.activeFamily?.id;
      this.api.pantry(undefined, true).subscribe({ next: items => this.personalItems = items, error: () => this.error = 'Unable to reach the API. Please sign in again.' });
      if (familyId) this.api.pantry(familyId).subscribe({ next: items => { this.householdItems = items.filter(item => item.family_id === familyId); this.loading = false; }, error: () => { this.error = 'Unable to reach the API. Please sign in again.'; this.loading = false; } }); else { this.householdItems = []; this.loading = false; }
      this.api.recommendations(familyId).subscribe({ next: ({ recommendations }) => this.recommendations = recommendations.slice(0, 3) });
    }, error: () => {
      this.api.pantry(undefined, true).subscribe({ next: items => { this.personalItems = items; this.householdItems = []; this.loading = false; }, error: () => { this.error = 'Unable to reach the API. Please sign in again.'; this.loading = false; } });
      this.api.recommendations().subscribe({ next: ({ recommendations }) => this.recommendations = recommendations.slice(0, 3) });
    } });
  }

  attention(items: PantryItem[]): PantryItem[] { const limit = Date.now() + 3 * 24 * 60 * 60 * 1000; return items.filter(item => item.freshness_status === 'review' || (item.expiry_date && new Date(item.expiry_date).getTime() <= limit)).slice(0, 4); }
}
