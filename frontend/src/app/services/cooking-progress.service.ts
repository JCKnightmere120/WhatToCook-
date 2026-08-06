import { Injectable } from '@angular/core';

export interface CookingProgress {
  stepIndex: number;
}

/** Device-local, resumable cooking state. It deliberately has no server model. */
@Injectable({ providedIn: 'root' })
export class CookingProgressService {
  private readonly prefix = 'whattocook_cooking_progress';

  load(userId: number, mealPlanId: number, stepCount: number): CookingProgress {
    try {
      const stored = JSON.parse(localStorage.getItem(this.key(userId, mealPlanId)) || '{}') as Partial<CookingProgress>;
      const maximum = Math.max(0, stepCount - 1);
      return { stepIndex: Number.isInteger(stored.stepIndex) ? Math.min(Math.max(stored.stepIndex!, 0), maximum) : 0 };
    } catch {
      return { stepIndex: 0 };
    }
  }

  save(userId: number, mealPlanId: number, stepIndex: number): void {
    try {
      localStorage.setItem(this.key(userId, mealPlanId), JSON.stringify({ stepIndex }));
    } catch { /* Resuming is optional when device storage is unavailable. */ }
  }

  clear(userId: number, mealPlanId: number): void {
    localStorage.removeItem(this.key(userId, mealPlanId));
  }

  private key(userId: number, mealPlanId: number): string {
    return `${this.prefix}_${userId}_${mealPlanId}`;
  }
}
