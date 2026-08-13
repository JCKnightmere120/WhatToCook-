import { Component } from '@angular/core';
import { ApiService, Profile } from '../services/api.service';
import { AuthService } from '../services/auth.service';

type Field = 'health_conditions' | 'allergies' | 'dietary_restrictions' | 'likes' | 'dislikes';

@Component({ selector: 'app-profile', templateUrl: './profile.page.html', styleUrls: ['./profile.page.scss'], standalone: false })
export class ProfilePage {
  values: Record<Field, string> = { health_conditions: '', allergies: '', dietary_restrictions: '', likes: '', dislikes: '' };
  visibility: Record<string, boolean> = { health_conditions: false, allergies: false, dietary_restrictions: false, likes: false, dislikes: false };
  saving = false;
  loading = false;
  message = '';
  loadError = '';
  readonly fields: Array<{ key: Field; label: string; hint: string }> = [
    { key: 'health_conditions', label: 'Health conditions', hint: 'e.g. hypertension, diabetes' },
    { key: 'allergies', label: 'Allergies', hint: 'e.g. peanuts, shellfish' },
    { key: 'dietary_restrictions', label: 'Dietary restrictions', hint: 'e.g. halal, vegetarian' },
    { key: 'likes', label: 'Food likes', hint: 'e.g. chicken adobo, vegetables' },
    { key: 'dislikes', label: 'Food dislikes', hint: 'e.g. ampalaya' },
  ];

  constructor(private api: ApiService, public auth: AuthService) {}

  ionViewWillEnter(): void {
    this.load();
  }

  load(): void {
    this.loading = true;
    this.loadError = '';
    this.api.profile().subscribe({
      next: profile => {
        this.fill(profile);
        this.loading = false;
      },
      error: () => {
        this.loadError = 'Could not load your profile. Check your connection and try again.';
        this.loading = false;
      },
    });
  }

  save(): void {
    const profile: Partial<Profile> = {};
    this.fields.forEach(field => profile[field.key] = this.toArray(this.values[field.key]));
    this.saving = true;
    this.message = '';
    this.api.updateProfile({ ...profile, visible_to_family: this.visibility }).subscribe({
      next: saved => {
        this.fill(saved);
        this.saving = false;
        this.message = 'Your profile has been saved.';
      },
      error: () => {
        this.saving = false;
        this.message = 'Could not save your profile.';
      },
    });
  }

  private fill(profile: Partial<Profile>): void {
    this.fields.forEach(field => {
      this.values[field.key] = (profile[field.key] || []).join(', ');
      this.visibility[field.key] = profile.visible_to_family?.[field.key] ?? false;
    });
  }

  private toArray(value: string): string[] {
    return value.split(',').map(item => item.trim()).filter(Boolean);
  }
}
