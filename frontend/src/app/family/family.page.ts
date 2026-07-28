import { Component } from '@angular/core';
import { ApiService, Family, HouseholdProfile } from '../services/api.service';
import { AuthService } from '../services/auth.service';

type DietField = 'health_conditions' | 'allergies' | 'dietary_restrictions' | 'likes' | 'dislikes';
interface DinerForm { id: number | null; user_id: number | null; name: string; relation: string; sex: string; birth_date: string; height_cm: string; weight_kg: string; activity_level: string; goal: string; health_conditions: string; allergies: string; dietary_restrictions: string; likes: string; dislikes: string; visible_to_family: Record<string, boolean>; }

@Component({ selector: 'app-family', templateUrl: './family.page.html', styleUrls: ['./family.page.scss'], standalone: false })
export class FamilyPage {
  families: Family[] = [];
  selected?: Family;
  profiles: HouseholdProfile[] = [];
  newFamilyName = '';
  inviteEmail = '';
  message = '';
  loading = false;
  showProfileForm = false;
  savingProfile = false;
  diner: DinerForm = this.emptyDiner();
  readonly fields: Array<{ key: DietField; label: string; hint: string }> = [
    { key: 'health_conditions', label: 'Health conditions', hint: 'e.g. diabetes, hypertension' },
    { key: 'allergies', label: 'Allergies', hint: 'e.g. peanuts, shellfish' },
    { key: 'dietary_restrictions', label: 'Dietary restrictions', hint: 'e.g. halal, vegetarian' },
    { key: 'likes', label: 'Food likes', hint: 'e.g. chicken adobo' },
    { key: 'dislikes', label: 'Food dislikes', hint: 'e.g. ampalaya' },
  ];

  constructor(private api: ApiService, public auth: AuthService) {}
  ionViewWillEnter(): void { this.load(); }
  get isOwner(): boolean { return !!this.selected && this.selected.owner_id === this.auth.user?.id; }
  canManage(profile: HouseholdProfile): boolean { return this.isOwner || profile.user_id === this.auth.user?.id; }

  load(): void {
    this.loading = true;
    this.api.families().subscribe({ next: families => {
      this.families = families;
      const id = Number(localStorage.getItem('whattocook_active_family'));
      this.selected = families.find(family => family.id === id) || families[0];
      if (this.selected) this.select(this.selected); else this.loading = false;
    }, error: () => { this.message = 'Could not load your family accounts.'; this.loading = false; } });
  }
  select(family: Family): void { this.selected = family; localStorage.setItem('whattocook_active_family', String(family.id)); this.loadProfiles(); }
  loadProfiles(): void { if (!this.selected) return; this.api.householdProfiles(this.selected.id).subscribe({ next: response => { this.profiles = response.household_profiles; this.loading = false; }, error: () => { this.message = 'Could not load household profiles.'; this.loading = false; } }); }
  create(): void { if (!this.newFamilyName.trim()) return; this.api.createFamily(this.newFamilyName.trim()).subscribe({ next: family => { this.newFamilyName = ''; this.families = [...this.families, family]; this.select(family); this.message = 'Family account created.'; }, error: () => this.message = 'Could not create the family account.' }); }
  invite(): void { if (!this.selected || !this.inviteEmail.trim()) return; this.api.addFamilyMember(this.selected.id, this.inviteEmail.trim()).subscribe({ next: () => { this.inviteEmail = ''; this.message = 'Family member added.'; this.load(); }, error: error => this.message = error?.error?.message || 'That account could not be added. It must be registered first.' }); }
  async copyJoinCode(): Promise<void> { if (!this.selected?.join_code) return; try { await navigator.clipboard.writeText(this.selected.join_code); this.message = 'Household code copied.'; } catch { this.message = 'Could not copy automatically. Select and copy the code manually.'; } }
  removeMember(member: NonNullable<Family['members']>[number]): void {
    if (!this.selected || member.role === 'owner' || !confirm(`Remove ${member.user?.name || 'this member'} from ${this.selected.name}?`)) return;
    this.api.removeFamilyMember(this.selected.id, member.user_id).subscribe({
      next: () => { this.message = 'Family member removed.'; this.load(); },
      error: error => this.message = error?.error?.message || 'Could not remove the family member.',
    });
  }
  startProfile(profile?: HouseholdProfile): void {
    this.diner = profile ? this.fromProfile(profile) : this.emptyDiner();
    this.showProfileForm = true;
  }
  cancelProfile(): void { this.showProfileForm = false; this.diner = this.emptyDiner(); }
  saveDiner(): void {
    if (!this.selected || !this.diner.name.trim()) { this.message = 'A diner name is required.'; return; }
    this.savingProfile = true;
    const payload: Partial<HouseholdProfile> = {
      user_id: this.diner.user_id, name: this.diner.name.trim(), relation: this.diner.relation || null, sex: this.diner.sex || null,
      birth_date: this.diner.birth_date || null, height_cm: this.numberOrNull(this.diner.height_cm), weight_kg: this.numberOrNull(this.diner.weight_kg),
      activity_level: this.diner.activity_level || null, goal: this.diner.goal || null, visible_to_family: this.diner.visible_to_family,
    };
    this.fields.forEach(field => payload[field.key] = this.toArray(this.diner[field.key]));
    const request = this.diner.id
      ? this.api.updateHouseholdProfile(this.selected.id, this.diner.id, payload)
      : this.api.createHouseholdProfile(this.selected.id, payload);
    request.subscribe({ next: () => { this.savingProfile = false; this.showProfileForm = false; this.diner = this.emptyDiner(); this.message = 'Diner profile saved.'; this.loadProfiles(); }, error: error => { this.savingProfile = false; this.message = error?.error?.message || 'Could not save the diner profile.'; } });
  }
  removeProfile(profile: HouseholdProfile): void { if (!this.selected || !confirm(`Remove ${profile.name}'s household profile?`)) return; this.api.deleteHouseholdProfile(this.selected.id, profile.id).subscribe({ next: () => { this.message = 'Diner profile removed.'; this.loadProfiles(); }, error: () => this.message = 'Could not remove the diner profile.' }); }
  private emptyDiner(): DinerForm { return { id: null, user_id: null, name: '', relation: '', sex: '', birth_date: '', height_cm: '', weight_kg: '', activity_level: '', goal: '', health_conditions: '', allergies: '', dietary_restrictions: '', likes: '', dislikes: '', visible_to_family: { health_conditions: false, allergies: false, dietary_restrictions: false, likes: false, dislikes: false } }; }
  private fromProfile(profile: HouseholdProfile): DinerForm { const form = this.emptyDiner(); form.id = profile.id; form.user_id = profile.user_id ?? null; form.name = profile.name; form.relation = profile.relation || ''; form.sex = profile.sex || ''; form.birth_date = profile.birth_date ? profile.birth_date.slice(0, 10) : ''; form.height_cm = profile.height_cm?.toString() || ''; form.weight_kg = profile.weight_kg?.toString() || ''; form.activity_level = profile.activity_level || ''; form.goal = profile.goal || ''; this.fields.forEach(field => { form[field.key] = (profile[field.key] || []).join(', '); form.visible_to_family[field.key] = profile.visible_to_family?.[field.key] ?? false; }); return form; }
  private toArray(value: string): string[] { return value.split(',').map(item => item.trim()).filter(Boolean); }
  private numberOrNull(value: string): number | null { return value.trim() ? Number(value) : null; }
}
