import { Component } from '@angular/core';
import { ThemeService } from '../services/theme.service';
import { firstValueFrom } from 'rxjs';
import { ApiService, PantryItem } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';
import { FreshnessReminderService } from '../services/freshness-reminder.service';

@Component({ selector: 'app-settings', templateUrl: './settings.page.html', styleUrls: ['./settings.page.scss'], standalone: false })
export class SettingsPage {
  reminderEnabled = false;
  reminderMessage = '';
  savingReminders = false;
  constructor(public theme: ThemeService, public reminders: FreshnessReminderService, private api: ApiService, private auth: AuthService, private context: HouseholdContextService) {}
  ionViewWillEnter(): void { this.reminderEnabled = this.reminders.enabled; }
  async changeReminders(enabled: boolean): Promise<void> {
    this.savingReminders = true;
    this.reminderMessage = '';
    try {
      const userId = this.auth.user?.id;
      if (!userId) throw new Error();
      const household = await firstValueFrom(this.context.refresh(userId));
      const personal = await firstValueFrom(this.api.pantry(undefined, true));
      const shared = household.activeFamily ? await firstValueFrom(this.api.pantry(household.activeFamily.id)) : [];
      const status = await this.reminders.setEnabled(enabled, [...personal, ...shared] as PantryItem[]);
      this.reminderEnabled = status === 'scheduled' || status === 'nothing_due';
      this.reminderMessage = ({ off: 'Freshness reminders are off.', scheduled: 'Freshness reminders are scheduled for your dated pantry items.', nothing_due: 'Reminders are on, but no future pantry dates are available yet.', not_available: 'Local notifications are available only in the installed Android app.', permission_denied: 'Notification permission was not granted.', error: 'Could not update reminders. Please try again.' })[status];
    } catch { this.reminderEnabled = this.reminders.enabled; this.reminderMessage = 'Could not update reminders. Please try again.'; }
    finally { this.savingReminders = false; }
  }
}
