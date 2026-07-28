import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { ApiService } from '../services/api.service';

@Component({ selector: 'app-onboarding', templateUrl: './onboarding.page.html', styleUrls: ['./onboarding.page.scss'], standalone: false })
export class OnboardingPage {
  mode: 'create' | 'join' = 'create';
  householdName = '';
  joinCode = '';
  allergies = '';
  dietaryRestrictions = '';
  submitting = false;
  error = '';
  constructor(private api: ApiService, private router: Router) {}
  createFamily(): void {
    if (!this.householdName.trim()) { this.error = 'Give your household a name first.'; return; }
    this.submitting = true; this.error = '';
    this.api.createFamily(this.householdName.trim()).subscribe({ next: family => { localStorage.setItem('whattocook_active_family', String(family.id)); this.completeSetup(); }, error: () => { this.error = 'Could not create your family account. Please try again.'; this.submitting = false; } });
  }
  joinFamily(): void {
    const code = this.joinCode.trim().toUpperCase();
    if (!/^[A-Z0-9]{8}$/.test(code)) { this.error = 'Enter the 8-character household invite code.'; return; }
    this.submitting = true; this.error = '';
    this.api.joinFamily(code).subscribe({ next: ({ family }) => { localStorage.setItem('whattocook_active_family', String(family.id)); this.completeSetup(); }, error: error => { this.error = error?.error?.message || 'Could not join this household. Check the invite code.'; this.submitting = false; } });
  }
  chooseMode(mode: 'create' | 'join'): void { this.mode = mode; this.error = ''; }
  continueSolo(): void { this.completeSetup(); }
  private completeSetup(): void { const split = (value: string) => value.split(',').map(item => item.trim()).filter(Boolean); this.api.updateProfile({ allergies: split(this.allergies), dietary_restrictions: split(this.dietaryRestrictions) }).subscribe({ next: () => this.router.navigateByUrl('/tabs/dashboard', { replaceUrl: true }), error: () => this.router.navigateByUrl('/tabs/dashboard', { replaceUrl: true }) }); }
}
