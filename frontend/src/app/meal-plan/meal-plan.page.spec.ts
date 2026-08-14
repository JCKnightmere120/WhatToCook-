import { ActivatedRoute, convertToParamMap, Router } from '@angular/router';
import { Observable, of, Subject } from 'rxjs';
import { ApiService, MealPlan, MealPlanPreflight } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService, HouseholdContextState } from '../services/household-context.service';
import { MealPlanPage } from './meal-plan.page';

describe('MealPlanPage date selection', () => {
  let component: MealPlanPage;

  beforeEach(() => {
    component = new MealPlanPage({} as never, {} as never, { user: { id: 1 } } as never, {} as never, {
      queryParamMap: of(convertToParamMap({})),
    } as never, {} as never);
    (component as any).periodStart = (component as any).startOfWeek(new Date());
    (component as any).setDays();
  });

  it('opens the first day for a week with no planned meals', () => {
    (component as any).selectUsefulDay();
    expect(component.dateOnly(component.selectedDay!)).toBe(component.dateOnly(component.days[0]));
  });

  it('opens the nearest upcoming planned day when today has no meal', () => {
    const upcoming = component.days.find(day => component.dateOnly(day) > component.dateOnly(new Date())) || component.days[6];
    component.plans = [planFor(component, upcoming)];

    (component as any).selectUsefulDay();

    expect(component.dateOnly(component.selectedDay!)).toBe(component.dateOnly(upcoming));
  });

  it('prefers today when a partially planned week includes a meal today', () => {
    const today = component.days.find(day => component.dateOnly(day) === component.dateOnly(new Date()));
    if (!today) { pending('Today is outside the local week.'); return; }
    component.plans = [planFor(component, component.days[6]), planFor(component, today, 2)];

    (component as any).selectUsefulDay();

    expect(component.dateOnly(component.selectedDay!)).toBe(component.dateOnly(today));
  });

  it('counts all meals in a fully generated week', () => {
    component.plans = component.days.reduce((plans: any[], day: Date) => plans.concat([planFor(component, day, 1, 'breakfast'), planFor(component, day, 2, 'lunch'), planFor(component, day, 3, 'dinner')]), []);
    expect(component.plannedMealCount).toBe(21);
    expect(component.allPlannedDays.length).toBe(7);
  });
});

function planFor(component: MealPlanPage, day: Date, id = 1, mealType = 'dinner'): any {
  return { id, recipe_id: id, planned_date: component.dateOnly(day), meal_type: mealType, servings: 1 };
}

describe('MealPlanPage request bounds', () => {
  let api: jasmine.SpyObj<ApiService>;
  let context: jasmine.SpyObj<HouseholdContextService>;
  let component: MealPlanPage;

  beforeEach(() => {
    api = jasmine.createSpyObj<ApiService>('ApiService', ['recommendations', 'mealPlans', 'mealPlanPreflight']);
    Object.defineProperty(api, 'hasToken', { value: true });
    api.recommendations.and.returnValue(of({ recommendations: [] }));
    api.mealPlans.and.returnValue(of([]));
    api.mealPlanPreflight.and.callFake(planId => of({ can_cook_from_pantry: planId % 2 === 0 } as unknown as MealPlanPreflight));
    context = jasmine.createSpyObj<HouseholdContextService>('HouseholdContextService', ['refresh']);
    context.refresh.and.returnValue(of(personalContext()));
    component = new MealPlanPage(
      api,
      context,
      { user: { id: 1 } } as AuthService,
      jasmine.createSpyObj<Router>('Router', ['navigate']),
      { queryParamMap: of(convertToParamMap({})) } as unknown as ActivatedRoute,
      {} as never,
    );
  });

  it('loads only the visible week and preflights no more than its selected day', () => {
    const today = component.dateOnly(new Date());
    api.mealPlans.and.returnValue(of([
      meal(1, today, 'breakfast'),
      meal(2, today, 'lunch'),
      meal(3, today, 'dinner'),
      meal(4, '2001-01-01', 'dinner'),
      meal(5, '2099-01-01', 'dinner'),
    ]));

    component.load();

    const start = component.dateOnly((component as any).periodStart);
    const end = component.dateOnly((component as any).addDays((component as any).periodStart, 6));
    expect(api.mealPlans).toHaveBeenCalledWith(undefined, start, end);
    expect(api.mealPlanPreflight.calls.allArgs()).toEqual([[1], [2], [3]]);
  });

  it('keeps one same-week load in flight when lifecycle hooks fire twice', () => {
    const refresh = new Subject<HouseholdContextState>();
    context.refresh.and.returnValue(refresh);

    component.ionViewWillEnter();
    component.ionViewWillEnter();

    expect(context.refresh).toHaveBeenCalledTimes(1);
  });

  it('cancels an in-flight readiness request when the page is left', () => {
    const today = component.dateOnly(new Date());
    let cancelled = false;
    api.mealPlans.and.returnValue(of([meal(1, today, 'dinner')]));
    api.mealPlanPreflight.and.returnValue(new Observable(() => () => { cancelled = true; }));

    component.load();
    component.ionViewWillLeave();

    expect(cancelled).toBeTrue();
  });
});

function personalContext(): HouseholdContextState {
  return { userId: 1, families: [], activeFamily: null };
}

function meal(id: number, plannedDate: string, mealType: string): MealPlan {
  return { id, recipe_id: id, planned_date: plannedDate, meal_type: mealType, servings: 1 };
}
