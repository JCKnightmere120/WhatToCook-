import { Component } from '@angular/core';
import { ApiService, Family, MealHistoryItem } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';
import { ExportLine, ExportService } from '../services/export.service';

@Component({ selector: 'app-meal-history', templateUrl: './meal-history.page.html', styleUrls: ['./meal-history.page.scss'], standalone: false })
export class MealHistoryPage {
  items: MealHistoryItem[] = [];
  household?: Family;
  loading = false;
  message = '';
  loadError = '';

  constructor(private api: ApiService, private auth: AuthService, private context: HouseholdContextService, private exports: ExportService) {}

  ionViewWillEnter(): void {
    this.load();
  }

  load(): void {
    const id = this.auth.user?.id;
    if (!id) return;
    this.loading = true;
    this.loadError = '';
    this.context.refresh(id).subscribe({
      next: context => {
        this.household = context.activeFamily || undefined;
        this.api.mealHistory().subscribe({
          next: items => {
            this.items = this.household ? items.filter(item => item.family_id === this.household?.id) : items.filter(item => !item.family_id);
            this.loading = false;
          },
          error: () => {
            this.loadError = 'Could not load meal history. Check your connection and try again.';
            this.loading = false;
          },
        });
      },
      error: () => {
        this.loadError = 'Could not load your household. Check your connection and try again.';
        this.loading = false;
      },
    });
  }

  recipeName(item: MealHistoryItem): string {
    return item.recipe?.name || `Recipe #${item.recipe_id}`;
  }

  export(kind: 'text' | 'image' | 'pdf'): void {
    const title = `${this.household ? this.household.name + ' shared' : 'Personal'} meal history`;
    const lines: ExportLine[] = this.items.map(item => ({ title: this.recipeName(item), detail: `${item.prepared_at} · ${item.servings || 1} servings${item.notes ? ` · ${item.notes}` : ''}` }));
    if (kind === 'text') this.exports.downloadText('whattocook-meal-history.txt', lines);
    if (kind === 'image') this.exports.downloadImage('whattocook-meal-history.svg', title, lines);
    if (kind === 'pdf') this.exports.print(title, lines);
  }
}
