import { Injectable } from '@angular/core';
import { Capacitor } from '@capacitor/core';
import { LocalNotifications } from '@capacitor/local-notifications';
import { PantryItem } from './api.service';

export type ReminderStatus = 'off' | 'not_available' | 'permission_denied' | 'scheduled' | 'nothing_due' | 'error';

@Injectable({ providedIn: 'root' })
export class FreshnessReminderService {
  private readonly enabledKey = 'whattocook_freshness_reminders_enabled';
  private readonly idBase = 510000;
  get enabled(): boolean { return localStorage.getItem(this.enabledKey) === 'true'; }
  get nativeAvailable(): boolean { return Capacitor.isNativePlatform(); }

  async setEnabled(enabled: boolean, items: PantryItem[]): Promise<ReminderStatus> {
    if (!enabled) { localStorage.removeItem(this.enabledKey); await this.cancelManaged(); return 'off'; }
    if (!this.nativeAvailable) return 'not_available';
    const permission = await LocalNotifications.requestPermissions();
    if (permission.display !== 'granted') return 'permission_denied';
    localStorage.setItem(this.enabledKey, 'true');
    return this.schedule(items);
  }

  async schedule(items: PantryItem[]): Promise<ReminderStatus> {
    if (!this.enabled || !this.nativeAvailable) return this.enabled ? 'not_available' : 'off';
    try {
      await this.cancelManaged();
      const now = new Date();
      const notifications: Array<{ id: number; title: string; body: string; schedule: { at: Date }; extra: { pantry_item_id: number; type: string } }> = [];
      items.forEach((item: PantryItem) => this.datesFor(item).forEach(({ date, type }: { date: string; type: string }, index: number) => {
        const at = new Date(`${date}T09:00:00`);
        if (!Number.isNaN(at.getTime()) && at > now) notifications.push({ id: this.idBase + item.id * 2 + index, title: 'Freshness reminder', body: `${item.name}: ${type}. Check it before cooking.`, schedule: { at }, extra: { pantry_item_id: item.id, type } });
      }));
      if (!notifications.length) return 'nothing_due';
      await LocalNotifications.schedule({ notifications });
      return 'scheduled';
    } catch { return 'error'; }
  }

  private datesFor(item: PantryItem): Array<{ date: string; type: string }> {
    const dates = [item.expiry_date ? { date: item.expiry_date.slice(0, 10), type: item.is_expiry_estimated ? 'estimated freshness date' : 'expiry date' } : undefined, item.freshness_review_date ? { date: item.freshness_review_date.slice(0, 10), type: 'freshness review date' } : undefined].filter((value): value is { date: string; type: string } => !!value);
    return dates.filter((value, index) => dates.findIndex(other => other.date === value.date) === index);
  }

  private async cancelManaged(): Promise<void> {
    if (!this.nativeAvailable) return;
    const pending = await LocalNotifications.getPending();
    const managed = pending.notifications.filter(notification => notification.id >= this.idBase);
    if (managed.length) await LocalNotifications.cancel({ notifications: managed });
  }
}
