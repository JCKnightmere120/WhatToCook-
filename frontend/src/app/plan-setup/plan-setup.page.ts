import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { ApiService, Family, HouseholdProfile, MealPlanBatchGenerationPayload } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';

type MealType = 'breakfast' | 'lunch' | 'dinner';
type AttendanceMode = 'every_day' | 'weekdays' | 'weekends' | 'specific_days';
type PlanContext = 'personal' | number;
type ChildMealMode = 'family_meal_with_adaptation' | 'separate_child_meal' | 'exclude';
type PlanLength = 1 | 2 | 3 | 4;

interface AttendanceEditor {
  diner: HouseholdProfile;
  originalMode: AttendanceMode;
  originalDates: string[];
  selectedDates: string[];
}

@Component({
  selector: 'app-plan-setup',
  templateUrl: './plan-setup.page.html',
  styleUrls: ['./plan-setup.page.scss'],
  standalone: false,
})
export class PlanSetupPage {
  readonly planLengths: PlanLength[] = [1, 2, 3, 4];
  readonly mealTypes: Array<{ value: MealType; label: string }> = [
    { value: 'breakfast', label: 'Breakfast' },
    { value: 'lunch', label: 'Lunch' },
    { value: 'dinner', label: 'Dinner' },
  ];
  readonly attendanceOptions: Array<{ value: AttendanceMode; label: string }> = [
    { value: 'every_day', label: 'Every day' },
    { value: 'weekdays', label: 'Weekdays only' },
    { value: 'weekends', label: 'Weekends only' },
    { value: 'specific_days', label: 'Specific days' },
  ];

  readonly minimumDate = this.toDateOnly(new Date());
  startDate = this.minimumDate;
  endDate = this.toDateOnly(this.addDays(new Date(), 6));
  planLength: PlanLength = 1;
  mealTypesSelected: MealType[] = ['dinner'];
  cookingTimeBudget: '15' | '30' | '45' | '60' | '90+' = '60';
  timePreference: 'strict' | 'flexible' = 'flexible';
  leftoverStrategy: 'avoid_leftovers' | 'reuse_ulam' | 'main_with_rice_side' = 'avoid_leftovers';
  dietPreference = 'saved-profile';
  servings = 1;
  contextId: PlanContext = 'personal';
  families: Family[] = [];
  household?: Family;
  diners: HouseholdProfile[] = [];
  selectedDinerIds: number[] = [];
  attendanceModes: Record<number, AttendanceMode> = {};
  specificAttendanceDates: Record<number, string[]> = {};
  childMealModes: Record<number, ChildMealMode> = {};
  attendanceEditor?: AttendanceEditor;
  loading = false;
  generating = false;
  generationStep = 0;
  message = '';
  conflictMessage = '';
  showConflictChoice = false;
  private modalReturnFocus?: HTMLElement;
  readonly conflictAlertButtons = [
    { text: 'Cancel', role: 'cancel' },
    { text: 'Review existing', handler: () => this.router.navigateByUrl('/tabs/meal-plan') },
    { text: 'Generate draft anyway', handler: () => this.generateDespiteConflicts() },
  ];

  constructor(
    private api: ApiService,
    private auth: AuthService,
    private householdContext: HouseholdContextService,
    private router: Router,
  ) {}

  ionViewWillEnter(): void {
    this.loadInitialContext();
  }

  onContextChange(): void {
    const userId = this.auth.user?.id;
    const family = this.contextId === 'personal'
      ? null
      : this.families.find(item => item.id === Number(this.contextId)) || null;
    if (userId) this.householdContext.select(userId, family);
    this.loadDiners(family);
  }

  toggleMealType(type: MealType): void {
    this.mealTypesSelected = this.mealTypesSelected.includes(type)
      ? this.mealTypesSelected.filter(item => item !== type)
      : [...this.mealTypesSelected, type];
  }

  setPlanLength(length: PlanLength): void {
    this.planLength = length;
    const start = this.dateFromInput(this.startDate) || new Date();
    this.endDate = this.toDateOnly(this.addDays(start, (length * 7) - 1));
  }

  isMealTypeSelected(type: MealType): boolean {
    return this.mealTypesSelected.includes(type);
  }

  setDinerSelected(dinerId: number, selected: boolean): void {
    this.selectedDinerIds = selected
      ? [...new Set([...this.selectedDinerIds, dinerId])]
      : this.selectedDinerIds.filter(id => id !== dinerId);
    if (!this.attendanceModes[dinerId]) this.attendanceModes[dinerId] = 'every_day';
    this.ensureServingsCoverDiners();
  }

  isDinerSelected(dinerId: number): boolean {
    return this.selectedDinerIds.includes(dinerId);
  }

  attendanceFor(dinerId: number): AttendanceMode {
    return this.attendanceModes[dinerId] || 'every_day';
  }

  setAttendance(dinerId: number, mode: AttendanceMode, diner?: HouseholdProfile): void {
    const previousMode = this.attendanceFor(dinerId);
    this.attendanceModes[dinerId] = mode;
    if (mode !== 'specific_days') return;

    const selectedDates = this.specificAttendanceDates[dinerId]?.length
      ? this.datesWithinPlan(this.specificAttendanceDates[dinerId])
      : this.datesInRange().filter(date => this.attendsOnWithMode(previousMode, dinerId, date));
    this.specificAttendanceDates[dinerId] = selectedDates;
    if (diner) this.openAttendanceEditor(diner, previousMode, selectedDates);
  }

  openAttendanceEditor(diner: HouseholdProfile, originalMode = this.attendanceFor(diner.id), originalDates = this.specificAttendanceDates[diner.id] || []): void {
    this.rememberModalReturnFocus();
    this.attendanceEditor = {
      diner,
      originalMode,
      originalDates: [...originalDates],
      selectedDates: [...(this.specificAttendanceDates[diner.id] || [])],
    };
  }

  isEditorDateSelected(date: string): boolean {
    return !!this.attendanceEditor?.selectedDates.includes(date);
  }

  toggleEditorDate(date: string, selected: boolean): void {
    if (!this.attendanceEditor) return;
    this.attendanceEditor.selectedDates = selected
      ? [...new Set([...this.attendanceEditor.selectedDates, date])]
      : this.attendanceEditor.selectedDates.filter(item => item !== date);
  }

  saveSpecificDays(): void {
    if (!this.attendanceEditor) return;
    this.specificAttendanceDates[this.attendanceEditor.diner.id] = this.datesWithinPlan(this.attendanceEditor.selectedDates);
    this.attendanceEditor = undefined;
  }

  cancelSpecificDays(): void {
    if (!this.attendanceEditor) return;
    const { diner, originalMode, originalDates } = this.attendanceEditor;
    this.attendanceModes[diner.id] = originalMode;
    this.specificAttendanceDates[diner.id] = [...originalDates];
    this.attendanceEditor = undefined;
  }
  onAttendanceEditorDidDismiss(): void { this.cancelSpecificDays(); this.restoreModalFocus(); }

  specificDaysSummary(dinerId: number): string {
    const dates = this.datesWithinPlan(this.specificAttendanceDates[dinerId] || []);
    if (!dates.length) return 'No dates selected yet';
    if (dates.length === 1) return this.dateLabel(dates[0]);
    return `${dates.length} selected dates`;
  }

  generate(): void {
    const formError = this.validateForm();
    if (formError) {
      this.message = formError;
      return;
    }

    const payload = this.generationPayload();
    this.generating = true;
    this.generationStep = 1;
    this.message = '';
    this.api.mealPlans(this.household?.id, this.startDate, this.endDate).subscribe({
      next: plans => {
        const requestedSlots = new Set(this.mealTypesSelected.map(type => `${type}`));
        const conflicts = plans.filter(plan => requestedSlots.has(plan.meal_type) && plan.status !== 'draft');
        this.generating = false;
        if (conflicts.length) {
          const dates = [...new Set(conflicts.map(plan => this.dateLabel(plan.planned_date.slice(0, 10))))];
          this.conflictMessage = `${conflicts.length} planned meal${conflicts.length === 1 ? '' : 's'} already use the selected slot${conflicts.length === 1 ? '' : 's'} on ${dates.join(', ')}.`;
          this.showConflictChoice = true;
          return;
        }
        this.submitGeneration(payload);
      },
      // If the optional preview cannot load, preserve the server's normal
      // generation path instead of blocking a user from creating a draft.
      error: () => { this.generating = false; this.submitGeneration(payload); },
    });
  }

  generateDespiteConflicts(): void {
    this.showConflictChoice = false;
    this.submitGeneration(this.generationPayload());
  }

  private generationPayload(): MealPlanBatchGenerationPayload {
    const attendance = this.household ? this.attendanceByDate() : undefined;
    return {
      start_date: this.startDate,
      end_date: this.endDate,
      meal_types: this.mealTypesSelected,
      cooking_time_budget: this.cookingTimeBudget,
      time_preference: this.timePreference,
      leftover_strategy: this.leftoverStrategy,
      servings: Math.max(1, Number(this.servings) || 1),
      ...(this.household ? {
        family_id: this.household.id,
        diner_profile_ids: this.selectedDinerIds,
        attendance_by_date: attendance,
        child_meal_modes: this.childMealModes,
      } : {}),
    };
  }

  private submitGeneration(payload: MealPlanBatchGenerationPayload): void {
    this.generating = true;
    this.generationStep = 2;
    this.message = '';
    this.api.generateMealPlanBatch(payload).subscribe({
      next: response => {
        this.generating = false;
        this.generationStep = 0;
        this.router.navigate(['/plan-review', response.batch.id], { replaceUrl: true });
      },
      error: error => {
        this.generating = false;
        this.generationStep = 0;
        this.message = this.errorMessage(error, 'Could not generate the meal-plan draft.');
      },
    });
  }

  private loadInitialContext(): void {
    const userId = this.auth.user?.id;
    if (!userId || !this.api.hasToken) {
      this.router.navigateByUrl('/auth', { replaceUrl: true });
      return;
    }

    this.loading = true;
    this.message = '';
    this.householdContext.refresh(userId).subscribe({
      next: context => {
        this.families = context.families;
        this.contextId = context.activeFamily?.id || 'personal';
        this.loadDiners(context.activeFamily);
      },
      error: error => {
        this.loading = false;
        this.message = this.errorMessage(error, 'Could not load your plan options.');
      },
    });
  }

  private loadDiners(family: Family | null): void {
    this.household = family || undefined;
    this.diners = [];
    this.selectedDinerIds = [];
    this.attendanceModes = {};
    this.specificAttendanceDates = {};
    this.childMealModes = {};
    this.attendanceEditor = undefined;

    if (!family) {
      this.servings = 1;
      this.loading = false;
      return;
    }

    this.loading = true;
    this.api.householdProfiles(family.id).subscribe({
      next: result => {
        this.diners = result.household_profiles;
        this.selectedDinerIds = this.diners.map(diner => diner.id);
        this.attendanceModes = this.diners.reduce<Record<number, AttendanceMode>>((modes, diner) => {
          modes[diner.id] = 'every_day';
          return modes;
        }, {});
        this.childMealModes = this.diners.filter(diner => this.isYoungChild(diner)).reduce<Record<number, ChildMealMode>>((modes, diner) => {
          modes[diner.id] = 'family_meal_with_adaptation';
          return modes;
        }, {});
        this.servings = Math.max(this.servings, this.selectedDinerIds.length, 1);
        this.loading = false;
      },
      error: error => {
        this.loading = false;
        this.message = this.errorMessage(error, 'Could not load household diner profiles.');
      },
    });
  }

  private validateForm(): string | undefined {
    const start = this.dateFromInput(this.startDate);
    const end = this.dateFromInput(this.endDate);
    const today = this.dateFromInput(this.minimumDate);
    if (!start || !end || !today) return 'Choose both a start date and an end date.';
    if (start < today) return 'A new plan must start today or later.';
    if (end < start) return 'The end date cannot be before the start date.';
    if (this.daysBetween(start, end) > 55) return 'A generated draft can cover up to 56 days.';
    if (!this.mealTypesSelected.length) return 'Choose at least one meal type.';
    if (Number(this.servings) < 1) return 'Servings must be at least 1.';
    if (!this.household) return undefined;
    if (!this.diners.length) return 'Add a diner profile in Family before creating a shared plan.';
    if (!this.selectedDinerIds.length) return 'Choose at least one household diner.';

    const noDinerDates = Object.entries(this.attendanceByDate())
      .filter(([, dinerIds]) => !dinerIds.length)
      .map(([date]) => date);
    return noDinerDates.length
      ? `No diner is attending on ${noDinerDates[0]}. Change attendance or shorten the date range.`
      : undefined;
  }

  private attendanceByDate(): Record<string, number[]> {
    return this.datesInRange().reduce<Record<string, number[]>>((attendance, date) => {
      attendance[date] = this.selectedDinerIds.filter(dinerId => this.attendsOn(dinerId, date));
      return attendance;
    }, {});
  }

  private attendsOn(dinerId: number, date: string): boolean {
    const mode = this.attendanceFor(dinerId);
    return this.attendsOnWithMode(mode, dinerId, date);
  }

  private attendsOnWithMode(mode: AttendanceMode, dinerId: number, date: string): boolean {
    if (mode === 'specific_days') return this.specificAttendanceDates[dinerId]?.includes(date) || false;
    const day = this.dateFromInput(date)?.getDay();
    if (mode === 'weekdays') return day !== 0 && day !== 6;
    if (mode === 'weekends') return day === 0 || day === 6;
    return true;
  }

  datesInRange(): string[] {
    const start = this.dateFromInput(this.startDate);
    const end = this.dateFromInput(this.endDate);
    if (!start || !end || end < start) return [];

    const dates: string[] = [];
    for (let date = new Date(start); date <= end; date = this.addDays(date, 1)) {
      dates.push(this.toDateOnly(date));
    }
    return dates;
  }

  private datesWithinPlan(dates: string[]): string[] {
    const planDates = new Set(this.datesInRange());
    return [...new Set(dates)].filter(date => planDates.has(date)).sort();
  }

  private rememberModalReturnFocus(): void { this.modalReturnFocus = document.activeElement instanceof HTMLElement ? document.activeElement : undefined; }
  private restoreModalFocus(): void { const target = this.modalReturnFocus; this.modalReturnFocus = undefined; if (target?.isConnected) setTimeout(() => target.focus(), 0); }

  dateLabel(date: string): string {
    const parsed = this.dateFromInput(date);
    return parsed
      ? new Intl.DateTimeFormat(undefined, { weekday: 'short', month: 'short', day: 'numeric' }).format(parsed)
      : date;
  }

  private ensureServingsCoverDiners(): void {
    if (this.household) this.servings = Math.max(Number(this.servings) || 1, this.selectedDinerIds.length, 1);
  }

  isYoungChild(diner: HouseholdProfile): boolean {
    return !!diner.age_band && diner.age_band !== '6_plus_years';
  }

  private dateFromInput(value: string): Date | undefined {
    const parsed = new Date(`${value}T12:00:00`);
    return Number.isNaN(parsed.getTime()) ? undefined : parsed;
  }

  private daysBetween(start: Date, end: Date): number {
    return Math.round((end.getTime() - start.getTime()) / 86_400_000);
  }

  private addDays(date: Date, days: number): Date {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
  }

  private toDateOnly(date: Date): string {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
  }

  private errorMessage(error: { error?: { message?: string; errors?: Record<string, string[]> } }, fallback: string): string {
    const validationErrors = error?.error?.errors;
    const validationMessage = validationErrors
      ? Object.keys(validationErrors).map(key => validationErrors[key][0]).find(message => !!message)
      : undefined;
    return validationMessage || error?.error?.message || fallback;
  }
}
