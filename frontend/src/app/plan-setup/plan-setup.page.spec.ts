import { Router } from '@angular/router';
import { of } from 'rxjs';
import {
  ApiService,
  Family,
  HouseholdProfile,
  MealPlanBatchGenerationPayload,
  MealPlanBatchResponse,
} from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';
import { PlanSetupPage } from './plan-setup.page';

describe('PlanSetupPage', () => {
  let api: jasmine.SpyObj<ApiService>;
  let router: jasmine.SpyObj<Router>;
  let component: PlanSetupPage;

  const family: Family = { id: 7, name: 'Deogracias Family', owner_id: 1 };
  const dad: HouseholdProfile = { id: 21, family_id: 7, name: 'Dad', relation: 'Father' };
  const son: HouseholdProfile = { id: 22, family_id: 7, name: 'Son', relation: 'Son' };
  const draftResponse: MealPlanBatchResponse = {
    batch: { id: 45, user_id: 1, family_id: 7, start_date: '2099-08-03', end_date: '2099-08-09', status: 'draft' },
    meal_plans: [],
    summary: { meal_count: 0, ready_count: 0, ingredients: [], shortages: [], needs_review: [] },
    conflicts: [],
  };

  beforeEach(() => {
    api = jasmine.createSpyObj<ApiService>('ApiService', ['generateMealPlanBatch', 'mealPlans']);
    api.mealPlans.and.returnValue(of([]));
    router = jasmine.createSpyObj<Router>('Router', ['navigate', 'navigateByUrl']);
    const auth = { user: { id: 1, name: 'Jacob', email: 'jacob@example.com' } } as AuthService;
    const context = jasmine.createSpyObj<HouseholdContextService>('HouseholdContextService', ['refresh', 'select']);
    component = new PlanSetupPage(api, auth, context, router);
  });

  it('sends per-date household attendance when it generates a draft', () => {
    component.household = family;
    component.contextId = family.id;
    component.diners = [dad, son];
    component.selectedDinerIds = [dad.id, son.id];
    component.attendanceModes = { [dad.id]: 'every_day', [son.id]: 'weekends' };
    component.startDate = '2099-08-03';
    component.endDate = '2099-08-09';
    component.mealTypesSelected = ['breakfast', 'dinner'];
    component.servings = 2;
    api.generateMealPlanBatch.and.returnValue(of(draftResponse));

    component.generate();

    const payload = api.generateMealPlanBatch.calls.mostRecent().args[0] as MealPlanBatchGenerationPayload;
    const dates = Object.keys(payload.attendance_by_date || {});
    const weekday = dates.find(date => {
      const day = new Date(`${date}T12:00:00`).getDay();
      return day > 0 && day < 6;
    });
    const weekend = dates.find(date => {
      const day = new Date(`${date}T12:00:00`).getDay();
      return day === 0 || day === 6;
    });

    expect(payload.family_id).toBe(family.id);
    expect(payload.diner_profile_ids).toEqual([dad.id, son.id]);
    expect(payload.meal_types).toEqual(['breakfast', 'dinner']);
    expect(weekday).toBeDefined();
    expect(weekend).toBeDefined();
    expect(payload.attendance_by_date?.[weekday as string]).toEqual([dad.id]);
    expect(payload.attendance_by_date?.[weekend as string]).toEqual([dad.id, son.id]);
    expect(router.navigate).toHaveBeenCalledWith(['/plan-review', draftResponse.batch.id], { replaceUrl: true });
  });

  it('blocks a shared plan when the attendance rules leave a day with no diner', () => {
    component.household = family;
    component.diners = [son];
    component.selectedDinerIds = [son.id];
    component.attendanceModes = { [son.id]: 'weekends' };
    component.startDate = '2099-08-03';
    component.endDate = '2099-08-09';

    component.generate();

    expect(api.generateMealPlanBatch).not.toHaveBeenCalled();
    expect(component.message).toContain('No diner is attending');
  });

  it('uses the exact dates selected for a household diner', () => {
    component.household = family;
    component.diners = [dad, son];
    component.selectedDinerIds = [dad.id, son.id];
    component.startDate = '2099-08-03';
    component.endDate = '2099-08-09';
    component.attendanceModes = { [dad.id]: 'every_day', [son.id]: 'every_day' };
    component.setAttendance(son.id, 'specific_days');
    component.specificAttendanceDates[son.id] = ['2099-08-08', '2099-08-09'];
    api.generateMealPlanBatch.and.returnValue(of(draftResponse));

    component.generate();

    const payload = api.generateMealPlanBatch.calls.mostRecent().args[0] as MealPlanBatchGenerationPayload;
    expect(payload.attendance_by_date?.['2099-08-07']).toEqual([dad.id]);
    expect(payload.attendance_by_date?.['2099-08-08']).toEqual([dad.id, son.id]);
    expect(payload.attendance_by_date?.['2099-08-09']).toEqual([dad.id, son.id]);
  });

  it('blocks a shared plan with no selected diner', () => {
    component.household = family;
    component.diners = [dad];
    component.selectedDinerIds = [];
    component.startDate = '2099-08-03';
    component.endDate = '2099-08-03';

    component.generate();

    expect(api.generateMealPlanBatch).not.toHaveBeenCalled();
    expect(component.message).toBe('Choose at least one household diner.');
  });

  it('warns before generating when requested meal slots are already planned', () => {
    component.startDate = '2099-08-03';
    component.endDate = '2099-08-03';
    component.mealTypesSelected = ['dinner'];
    api.mealPlans.and.returnValue(of([{
      id: 9, recipe_id: 3, planned_date: '2099-08-03', meal_type: 'dinner', servings: 1, status: 'scheduled',
    }]));

    component.generate();

    expect(component.showConflictChoice).toBeTrue();
    expect(component.conflictMessage).toContain('already use the selected slot');
    expect(api.generateMealPlanBatch).not.toHaveBeenCalled();
  });

  it('sets a weekly end date and keeps cuisine and equipment choices interactive', () => {
    component.startDate = '2099-08-03';

    component.setPlanLength(2);
    component.toggleCuisine('Asian');
    component.toggleEquipment('oven');

    expect(component.endDate).toBe('2099-08-16');
    expect(component.hasCuisine('Asian')).toBeTrue();
    expect(component.hasEquipment('oven')).toBeTrue();
  });
});
