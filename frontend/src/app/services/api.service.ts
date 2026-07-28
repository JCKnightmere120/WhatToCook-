import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Capacitor } from '@capacitor/core';
import { environment } from '../../environments/environment';

export interface PantryItem { id: number; name: string; quantity?: string; quantity_value?: number | string; unit?: string; purchase_date?: string; expiry_date?: string; purchase_source?: string; storage_type?: string; freshness_condition?: string; freshness_status?: 'fresh' | 'review' | 'spoiled' | 'used' | 'discarded'; freshness_review_date?: string; freshness_confidence?: 'high' | 'medium' | 'low'; is_expiry_estimated?: boolean; family_id?: number | null; last_used_quantity?: number | string | null; }
export interface Family { id: number; name: string; owner_id: number; join_code?: string; owner?: { id: number; name: string }; members?: Array<{ id: number; user_id: number; role: string; user?: { id: number; name: string; email: string } }>; }
export interface RecipeIngredient { name: string; quantity?: string; unit?: string; substitutes: string[]; }
export interface Recommendation { recipe: { id: number; name: string; servings?: number; prep_time?: number; cook_time?: number; }; match_percentage: number; available_ingredients: RecipeIngredient[]; missing_ingredients: RecipeIngredient[]; }
export interface RecipeDetail { id: number; name: string; description?: string; instructions?: string; cooking_tips?: string; servings?: number; prep_time?: number; cook_time?: number; ingredients: RecipeIngredient[]; }
export interface Profile { health_conditions: string[]; allergies: string[]; dietary_restrictions: string[]; likes: string[]; dislikes: string[]; visible_to_family: Record<string, boolean>; }
export interface HouseholdProfile { id: number; family_id: number; user_id?: number | null; name: string; relation?: string | null; sex?: string | null; birth_date?: string | null; height_cm?: number | string | null; weight_kg?: number | string | null; activity_level?: string | null; goal?: string | null; health_conditions?: string[]; allergies?: string[]; dietary_restrictions?: string[]; likes?: string[]; dislikes?: string[]; visible_to_family?: Record<string, boolean>; }
export interface FamilyInvitation { id: number; family_id: number; status: 'pending'; family: Family; }
export interface PantryInputCandidate { name: string; quantity?: string | null; unit?: string | null; purchase_source?: string; storage_type?: string; }
export interface PantryInputResult { source: 'barcode' | 'voice' | 'receipt'; needs_review: boolean; message: string; candidates: PantryInputCandidate[]; }

@Injectable({ providedIn: 'root' })
export class ApiService {
  constructor(private http: HttpClient) {}
  private get baseUrl(): string {
    return Capacitor.getPlatform() === 'android' && Capacitor.isNativePlatform()
      ? environment.androidApiBaseUrl
      : environment.apiBaseUrl;
  }
  setToken(token: string): void { localStorage.setItem('whattocook_token', token.trim()); }
  get hasToken(): boolean { return !!localStorage.getItem('whattocook_token'); }
  private options() { return { headers: new HttpHeaders({ Authorization: `Bearer ${localStorage.getItem('whattocook_token') ?? ''}` }) }; }
  pantry(familyId?: number, personal = false): Observable<PantryItem[]> { const params: Record<string, string> = familyId ? { family_id: String(familyId) } : (personal ? { personal: '1' } : {}); return this.http.get<PantryItem[]>(`${this.baseUrl}/pantry`, { ...this.options(), params }); }
  addPantry(item: Omit<PantryItem, 'id'>): Observable<{ item: PantryItem }> { return this.http.post<{ item: PantryItem }>(`${this.baseUrl}/pantry`, item, this.options()); }
  updatePantry(itemId: number, item: Partial<PantryItem>): Observable<{ item: PantryItem }> { return this.http.put<{ item: PantryItem }>(`${this.baseUrl}/pantry/${itemId}`, item, this.options()); }
  deletePantry(itemId: number): Observable<unknown> { return this.http.delete(`${this.baseUrl}/pantry/${itemId}`, this.options()); }
  updateFreshness(itemId: number, action: 'still_fresh' | 'spoiled' | 'used' | 'discarded' | 'undo_used', usedQuantity?: number): Observable<{ item: PantryItem }> { return this.http.patch<{ item: PantryItem }>(`${this.baseUrl}/pantry/${itemId}/freshness`, { action, ...(usedQuantity ? { used_quantity: usedQuantity } : {}) }, this.options()); }
  barcodeInput(barcode: string): Observable<PantryInputResult> { return this.http.post<PantryInputResult>(`${this.baseUrl}/pantry-inputs/barcode`, { barcode }, this.options()); }
  voiceInput(transcript: string): Observable<PantryInputResult> { return this.http.post<PantryInputResult>(`${this.baseUrl}/pantry-inputs/voice`, { transcript }, this.options()); }
  receiptInput(receipt: File, recognizedText = ''): Observable<PantryInputResult> { const data = new FormData(); data.append('receipt', receipt); if (recognizedText.trim()) data.append('recognized_text', recognizedText); return this.http.post<PantryInputResult>(`${this.baseUrl}/pantry-inputs/receipt`, data, this.options()); }
  families(): Observable<Family[]> { return this.http.get<Family[]>(`${this.baseUrl}/families`, this.options()); }
  createFamily(name: string): Observable<Family> { return this.http.post<Family>(`${this.baseUrl}/families`, { name }, this.options()); }
  joinFamily(joinCode: string): Observable<{ family: Family; joined: boolean }> { return this.http.post<{ family: Family; joined: boolean }>(`${this.baseUrl}/families/join`, { join_code: joinCode }, this.options()); }
  addFamilyMember(familyId: number, email: string): Observable<unknown> { return this.http.post(`${this.baseUrl}/families/${familyId}/members`, { email, role: 'member' }, this.options()); }
  invitations(): Observable<{ invitations: FamilyInvitation[] }> { return this.http.get<{ invitations: FamilyInvitation[] }>(`${this.baseUrl}/family-invitations`, this.options()); }
  acceptInvitation(invitationId: number): Observable<{ family: Family }> { return this.http.post<{ family: Family }>(`${this.baseUrl}/family-invitations/${invitationId}/accept`, {}, this.options()); }
  removeFamilyMember(familyId: number, userId: number): Observable<unknown> { return this.http.delete(`${this.baseUrl}/families/${familyId}/members/${userId}`, this.options()); }
  profile(): Observable<Profile> { return this.http.get<Profile>(`${this.baseUrl}/profile`, this.options()); }
  updateProfile(profile: Partial<Profile>): Observable<Profile> { return this.http.put<Profile>(`${this.baseUrl}/profile`, profile, this.options()); }
  householdProfiles(familyId: number): Observable<{ household_profiles: HouseholdProfile[] }> { return this.http.get<{ household_profiles: HouseholdProfile[] }>(`${this.baseUrl}/families/${familyId}/household-profiles`, this.options()); }
  createHouseholdProfile(familyId: number, profile: Partial<HouseholdProfile>): Observable<{ household_profile: HouseholdProfile }> { return this.http.post<{ household_profile: HouseholdProfile }>(`${this.baseUrl}/families/${familyId}/household-profiles`, profile, this.options()); }
  updateHouseholdProfile(familyId: number, profileId: number, profile: Partial<HouseholdProfile>): Observable<{ household_profile: HouseholdProfile }> { return this.http.put<{ household_profile: HouseholdProfile }>(`${this.baseUrl}/families/${familyId}/household-profiles/${profileId}`, profile, this.options()); }
  deleteHouseholdProfile(familyId: number, profileId: number): Observable<unknown> { return this.http.delete(`${this.baseUrl}/families/${familyId}/household-profiles/${profileId}`, this.options()); }
  recommendations(familyId?: number): Observable<{ recommendations: Recommendation[] }> { return this.http.get<{ recommendations: Recommendation[] }>(`${this.baseUrl}/recipes/recommendations`, { ...this.options(), params: familyId ? { family_id: familyId } : undefined }); }
  recipe(recipeId: number): Observable<RecipeDetail> { return this.http.get<RecipeDetail>(`${this.baseUrl}/recipes/${recipeId}`, this.options()); }
  addMissingToList(recipeId: number): Observable<unknown> { return this.http.post(`${this.baseUrl}/recipes/${recipeId}/shopping-list`, {}, this.options()); }
}
