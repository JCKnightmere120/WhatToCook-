import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { forkJoin, of } from 'rxjs';
import { catchError, switchMap } from 'rxjs/operators';
import { ApiService, Family, HouseholdProfile, MealPlan, Recommendation } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';

type MealType = 'Breakfast' | 'Lunch' | 'Dinner';
interface PlannerDraft { date: string; mealType: MealType; recipeId: number | null; servings: number; dinerIds: number[]; planId?: number; }

@Component({ selector: 'app-meal-plan', templateUrl: './meal-plan.page.html', styleUrls: ['./meal-plan.page.scss'], standalone: false })
export class MealPlanPage {
  readonly mealTypes: MealType[] = ['Breakfast', 'Lunch', 'Dinner'];
  days: Date[] = [];
  periodWeeks = 1;
  household?: Family;
  families: Family[] = [];
  contextId: number | 'personal' = 'personal';
  diners: HouseholdProfile[] = [];
  recipes: Recommendation[] = [];
  plans: MealPlan[] = [];
  readonly readinessByPlan = new Map<number, 'ready' | 'attention' | 'unavailable'>();
  draft?: PlannerDraft;
  loading = false;
  saving = false;
  deleteCandidate?: MealPlan;
  private deleteClosesEditor = false;
  readonly deleteAlertButtons = [
    { text: 'Cancel', role: 'cancel' },
    { text: 'Delete meal', role: 'destructive', handler: () => this.confirmRemove() },
  ];
  message = '';
  loadError = '';
  private periodStart = this.startOfWeek(new Date());

  constructor(private api: ApiService, private householdContext: HouseholdContextService, private auth: AuthService, private router: Router, private route: ActivatedRoute) {
    // A saved generated draft returns here with this value. Query parameters
    // update even when Ionic keeps the tab page alive in its navigation stack.
    this.route.queryParamMap.subscribe(params => {
      if (params.has('refresh')) this.load();
    });
  }
  ionViewWillEnter(): void { this.load(); }

  load(): void {
    const userId = this.auth.user?.id;
    if (!userId || !this.api.hasToken) return;
    this.loading = true;
    this.message = '';
    this.loadError = '';
    this.householdContext.refresh(userId).pipe(
      switchMap(context => {
        this.household = context.activeFamily || undefined;
        this.families = context.families;
        this.contextId = this.household?.id || 'personal';
        return this.household
          ? forkJoin({ profiles: this.api.householdProfiles(this.household.id), recommendations: this.api.recommendations(this.household.id), plans: this.api.mealPlans(this.household.id) })
          : forkJoin({ profiles: of({ household_profiles: [] as HouseholdProfile[] }), recommendations: this.api.recommendations(), plans: this.api.mealPlans() });
      }),
      catchError(() => { this.loadError = 'Could not load the meal planner. Check your connection and try again.'; return of({ profiles: { household_profiles: [] as HouseholdProfile[] }, recommendations: { recommendations: [] as Recommendation[] }, plans: [] as MealPlan[] }); }),
    ).subscribe(result => {
      this.diners = result.profiles.household_profiles;
      this.recipes = result.recommendations.recommendations;
      this.plans = this.household ? result.plans.filter(plan => plan.family_id === this.household?.id) : result.plans.filter(plan => !plan.family_id);
      this.setDays();
      this.loading = false;
      this.loadReadiness();
    });
  }

  previousPeriod(): void { this.periodStart = this.addDays(this.periodStart, -this.periodWeeks * 7); this.setDays(); }
  nextPeriod(): void { this.periodStart = this.addDays(this.periodStart, this.periodWeeks * 7); this.setDays(); }
  currentPeriod(): void { this.periodStart = this.startOfWeek(new Date()); this.setDays(); }
  changePeriod(): void { this.periodStart = this.startOfWeek(this.periodStart); this.setDays(); }
  planFor(day: Date, mealType: MealType): MealPlan | undefined { return this.plans.find(plan => this.dateOnly(plan.planned_date) === this.dateOnly(day) && plan.meal_type.toLowerCase() === mealType.toLowerCase()); }
  openSlot(day: Date, mealType: MealType, plan?: MealPlan): void {
    if (plan && this.isCooked(plan)) {
      this.openMealDetails(plan);
      return;
    }
    this.draft = { date: this.dateOnly(day), mealType, planId: plan?.id, recipeId: plan?.recipe_id || null, servings: plan?.servings || plan?.recipe?.servings || Math.max(this.diners.length, 1), dinerIds: plan?.diner_profile_ids || this.diners.map(diner => diner.id) };
  }
  closeEditor(): void { this.draft = undefined; }
  toggleDiner(id: number): void { if (!this.draft) return; this.draft.dinerIds = this.draft.dinerIds.includes(id) ? this.draft.dinerIds.filter(dinerId => dinerId !== id) : [...this.draft.dinerIds, id]; if (this.draft.dinerIds.length) this.draft.servings = Math.max(this.draft.servings, this.draft.dinerIds.length); }
  recipeName(plan: MealPlan): string { return plan.recipe?.name || this.recipes.find(item => item.recipe.id === plan.recipe_id)?.recipe.name || 'Selected recipe'; }
  servingsFor(plan: MealPlan): number { return plan.servings || plan.recipe?.servings || 1; }
  dinersFor(plan: MealPlan): string { const ids = plan.diner_profile_ids || []; const names = this.diners.filter(diner => ids.includes(diner.id)).map(diner => diner.name); return names.length ? names.join(', ') : this.household ? 'No diners selected' : 'Personal profile'; }
  get plannerName(): string { return this.household?.name || 'your personal profile'; }
  changeContext(): void {
    const userId = this.auth.user?.id;
    if (!userId) return;
    this.householdContext.select(userId, this.contextId === 'personal' ? null : this.families.find(family => family.id === this.contextId) || null);
    this.load();
  }
  openGeneration(): void { this.router.navigate(['/plan-setup']); }
  openMealDetails(plan: MealPlan): void { this.router.navigate(['/meal-details', plan.id]); }
  openShoppingList(): void { this.router.navigate(['/shopping-list']); }
  readiness(plan: MealPlan): 'ready' | 'attention' | 'unavailable' | 'cooked' { return this.isCooked(plan) ? 'cooked' : this.readinessByPlan.get(plan.id) || 'unavailable'; }
  readinessLabel(plan: MealPlan): string { return ({ ready: 'Pantry ready', attention: 'Needs groceries', cooked: 'Cooked', unavailable: 'Check pantry' })[this.readiness(plan)]; }

  save(): void {
    if (!this.draft?.recipeId) { this.message = 'Choose a recipe before saving this meal.'; return; }
    if (this.household && !this.draft.dinerIds.length) { this.message = 'Select at least one diner.'; return; }
    this.saving = true;
    const payload = { recipe_id: this.draft.recipeId, ...(this.household ? { family_id: this.household.id, diner_profile_ids: this.draft.dinerIds } : {}), planned_date: this.draft.date, meal_type: this.draft.mealType.toLowerCase(), servings: this.draft.servings };
    const request = this.draft.planId ? this.api.updateMealPlan(this.draft.planId, payload) : this.api.createMealPlan(payload);
    request.subscribe({ next: () => { this.message = 'Meal scheduled.'; this.draft = undefined; this.saving = false; this.load(); }, error: error => { this.message = error?.error?.message || 'Could not save this meal.'; this.saving = false; } });
  }
  requestRemove(plan: MealPlan): void { if (!this.isCooked(plan)) { this.deleteClosesEditor = false; this.deleteCandidate = plan; } }
  remove(): void { if (!this.draft?.planId) return; this.deleteClosesEditor = true; this.deleteCandidate = this.plans.find(plan => plan.id === this.draft?.planId); }
  confirmRemove(): void { if (this.deleteCandidate) this.deletePlan(this.deleteCandidate.id, this.deleteClosesEditor); }
  private deletePlan(planId: number, closeEditor: boolean): void { this.deleteCandidate = undefined; this.api.deleteMealPlan(planId).subscribe({ next: () => { if (closeEditor) this.draft = undefined; this.message = 'Meal removed.'; this.load(); }, error: error => this.message = error?.error?.message || 'Could not remove this meal.' }); }
  isCooked(plan: MealPlan): boolean { return !!plan.completed_at; }
  dateLabel(day: Date): string { return day.toLocaleDateString(undefined, { weekday: 'short' }); }
  dayNumber(day: Date): string { return day.toLocaleDateString(undefined, { day: 'numeric', month: 'short' }); }
  periodLabel(): string { return `${this.days[0]?.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) || ''} – ${this.days[this.days.length - 1]?.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) || ''}`; }
  private startOfWeek(date: Date): Date { const result = new Date(date); const offset = (result.getDay() + 6) % 7; result.setDate(result.getDate() - offset); result.setHours(0, 0, 0, 0); return result; }
  private setDays(): void { this.days = Array.from({ length: this.periodWeeks * 7 }, (_, index) => this.addDays(this.periodStart, index)); }
  private loadReadiness(): void {
    this.readinessByPlan.clear();
    const activePlans = this.plans.filter(plan => !this.isCooked(plan));
    if (!activePlans.length) return;
    forkJoin(activePlans.map(plan => this.api.mealPlanPreflight(plan.id).pipe(catchError(() => of(undefined))))).subscribe(checks => checks.forEach((check, index) => this.readinessByPlan.set(activePlans[index].id, check ? (check.can_cook_from_pantry ? 'ready' : 'attention') : 'unavailable')));
  }
  private addDays(date: Date, days: number): Date { const result = new Date(date); result.setDate(result.getDate() + days); return result; }
  private dateOnly(value: Date | string): string { return typeof value === 'string' ? value.slice(0, 10) : `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, '0')}-${String(value.getDate()).padStart(2, '0')}`; }
}
