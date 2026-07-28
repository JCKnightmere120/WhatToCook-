import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { map } from 'rxjs/operators';
import { ApiService, Family } from './api.service';

export interface HouseholdContextState {
  userId: number | null;
  families: Family[];
  activeFamily: Family | null;
}

/**
 * Keeps one consistent household selection across the app.
 *
 * A selection belongs to the signed-in account, not to the browser as a
 * whole. That prevents one household member's selection from affecting the
 * next account that signs in on the same device.
 */
@Injectable({ providedIn: 'root' })
export class HouseholdContextService {
  private readonly stateSubject = new BehaviorSubject<HouseholdContextState>({
    userId: null,
    families: [],
    activeFamily: null,
  });

  readonly state$ = this.stateSubject.asObservable();

  constructor(private api: ApiService) {}

  get snapshot(): HouseholdContextState {
    return this.stateSubject.value;
  }

  /** Loads accepted households and resolves a valid active household. */
  refresh(userId: number | undefined): Observable<HouseholdContextState> {
    if (!userId || !this.api.hasToken) {
      return of(this.publishState(null, [], null));
    }

    return this.api.families().pipe(
      map(families => {
        const savedFamilyId = this.readSavedFamilyId(userId);
        const activeFamily = families.find(family => family.id === savedFamilyId) || families[0] || null;
        return this.publishState(userId, families, activeFamily);
      }),
    );
  }

  /** Selects a household already returned for the current user. */
  select(userId: number | undefined, family: Family | null): void {
    if (!userId) {
      this.publishState(null, [], null);
      return;
    }

    const currentFamilies = this.stateSubject.value.userId === userId
      ? this.stateSubject.value.families
      : [];
    const families = family
      ? [...currentFamilies.filter(existing => existing.id !== family.id), family]
      : currentFamilies;
    this.publishState(userId, families, family);
  }

  private publishState(userId: number | null, families: Family[], activeFamily: Family | null): HouseholdContextState {
    if (userId) {
      const key = this.storageKey(userId);
      if (activeFamily) {
        localStorage.setItem(key, String(activeFamily.id));
      } else {
        localStorage.removeItem(key);
      }
    }

    const state = { userId, families, activeFamily };
    this.stateSubject.next(state);
    return state;
  }

  private readSavedFamilyId(userId: number): number | null {
    const savedId = Number(localStorage.getItem(this.storageKey(userId)));
    if (savedId) return savedId;

    // Migrate the selection used by earlier versions, if it belongs to one of
    // this account's accepted households. The family list validates it later.
    const legacyId = Number(localStorage.getItem('whattocook_active_family'));
    return legacyId || null;
  }

  private storageKey(userId: number): string {
    return `whattocook_active_family_${userId}`;
  }
}
