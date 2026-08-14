import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PantryItem } from './api.service';

export interface PantryChange {
  items: PantryItem[];
}

/**
 * Broadcasts confirmed pantry mutations to cached Ionic pages.
 *
 * The pantry route is retained by Ionic, so relying only on entering the
 * route can leave its in-memory lists stale after a purchase elsewhere in
 * the app. The created lots returned by the API are the source of truth for
 * this short-lived UI update; a normal pantry load still reconciles later.
 */
@Injectable({ providedIn: 'root' })
export class PantryChangeService {
  private readonly changesSubject = new Subject<PantryChange>();

  readonly changes$ = this.changesSubject.asObservable();

  publishAddedItems(items: PantryItem[], batchFamilyId: number | null | undefined): void {
    if (!items.length) return;

    this.changesSubject.next({
      items: items.map(item => ({
        ...item,
        // API responses include family_id. Keep an explicit null for a
        // personal lot; only use the batch scope for older/incomplete payloads.
        family_id: Object.prototype.hasOwnProperty.call(item, 'family_id')
          ? item.family_id ?? null
          : batchFamilyId ?? null,
      })),
    });
  }
}
