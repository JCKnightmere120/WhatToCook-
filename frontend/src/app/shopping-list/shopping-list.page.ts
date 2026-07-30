import { Component } from '@angular/core';
import { forkJoin } from 'rxjs';
import { ApiService, Family, ShoppingListItem } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';

@Component({ selector: 'app-shopping-list', templateUrl: './shopping-list.page.html', styleUrls: ['./shopping-list.page.scss'], standalone: false })
export class ShoppingListPage {
  items: ShoppingListItem[] = [];
  household?: Family;
  name = '';
  quantity = '';
  unit = '';
  message = '';
  loading = false;
  markingAll = false;
  confirmMarkAll = false;
  readonly markAllAlertButtons = [
    { text: 'Cancel', role: 'cancel' },
    { text: 'Mark all bought', role: 'confirm', handler: () => this.markAllBought() },
  ];

  constructor(private api: ApiService, private auth: AuthService, private context: HouseholdContextService) {}

  ionViewWillEnter(): void { this.load(); }

  get remainingItems(): ShoppingListItem[] { return this.items.filter(item => !item.is_purchased); }

  load(): void {
    const id = this.auth.user?.id;
    if (!id) return;
    this.loading = true;
    this.context.refresh(id).subscribe({
      next: context => {
        this.household = context.activeFamily || undefined;
        this.api.shoppingList().subscribe({
          next: items => { this.items = this.household ? items.filter(item => item.family_id === this.household?.id) : items.filter(item => !item.family_id); this.loading = false; },
          error: () => { this.message = 'Could not load the shopping list.'; this.loading = false; },
        });
      },
      error: () => { this.loading = false; this.message = 'Could not load your household.'; },
    });
  }

  add(): void {
    if (!this.name.trim()) return;
    this.api.addShoppingItem({ ingredient_name: this.name.trim(), quantity: this.quantity || null, unit: this.unit || null, is_purchased: false, family_id: this.household?.id || null }).subscribe({
      next: () => { this.name = this.quantity = this.unit = ''; this.load(); },
      error: () => this.message = 'Could not add the item.',
    });
  }

  toggle(item: ShoppingListItem): void {
    this.api.updateShoppingItem(item.id, { is_purchased: !item.is_purchased }).subscribe({
      next: updated => item.is_purchased = updated.is_purchased,
      error: () => this.message = 'Could not update the item.',
    });
  }

  markAllBought(): void {
    const remaining = this.remainingItems;
    this.confirmMarkAll = false;
    if (!remaining.length) return;
    this.markingAll = true;
    forkJoin(remaining.map(item => this.api.updateShoppingItem(item.id, { is_purchased: true }))).subscribe({
      next: () => { this.markingAll = false; this.items = this.items.map(item => ({ ...item, is_purchased: item.is_purchased || remaining.some(selected => selected.id === item.id) })); },
      error: () => { this.markingAll = false; this.message = 'Could not mark all items as bought. Please try again.'; },
    });
  }

  remove(item: ShoppingListItem): void {
    this.api.deleteShoppingItem(item.id).subscribe({
      next: () => this.items = this.items.filter(current => current.id !== item.id),
      error: () => this.message = 'Could not remove the item.',
    });
  }
}
