import { ActivatedRoute, Router } from '@angular/router';
import { of } from 'rxjs';
import { AuthService } from '../services/auth.service';
import { ApiService, MealPlanPreflight } from '../services/api.service';
import { CookingProgressService } from '../services/cooking-progress.service';
import { CookingPage } from './cooking.page';

describe('CookingPage', () => {
  let api: jasmine.SpyObj<ApiService>;
  let router: jasmine.SpyObj<Router>;
  let progress: jasmine.SpyObj<CookingProgressService>;
  let component: CookingPage;

  const preflight: MealPlanPreflight = {
    meal_plan: { id: 42, recipe_id: 9, planned_date: '2099-08-03', meal_type: 'dinner', servings: 3 },
    recipe: { id: 9, name: 'Chicken Tinola', instructions: '  Prepare the chicken.\n\n Simmer with ginger. \n   ', ingredients: [{ name: 'Chicken', quantity: '1', unit: 'kg', substitutes: [] }] },
    diners: [], pantry_scope: 'personal', can_cook_from_pantry: true, can_mark_cooked_without_deduction: true,
    match_percentage: 100, ingredients: [], ingredients_by_status: { ready: [], low_stock: [], missing: [], needs_review: [] },
  };

  beforeEach(() => {
    api = jasmine.createSpyObj<ApiService>('ApiService', ['mealPlanPreflight', 'cookMealPlan', 'completeMealPlanWithoutDeduction']);
    router = jasmine.createSpyObj<Router>('Router', ['navigate', 'navigateByUrl']);
    progress = jasmine.createSpyObj<CookingProgressService>('CookingProgressService', ['load', 'save', 'clear']);
    progress.load.and.returnValue({ stepIndex: 0 });
    const route = { snapshot: { paramMap: { get: () => '42' } } } as unknown as ActivatedRoute;
    component = new CookingPage(api, { user: { id: 7 } } as AuthService, progress, route, router);
  });

  it('parses only non-empty instruction lines and persists navigation', () => {
    api.mealPlanPreflight.and.returnValue(of(preflight));

    component.ionViewWillEnter();
    component.next();

    expect(component.steps).toEqual(['Prepare the chicken.', 'Simmer with ginger.']);
    expect(component.ingredientChecks).toEqual([false]);
    expect(component.progressLabel).toBe('Step 2 of 2');
    expect(progress.load).toHaveBeenCalledWith(7, 42, 2);
    expect(progress.save).toHaveBeenCalledWith(7, 42, 1);
  });

  it('keeps the ingredient checklist local to the guided cooking session', () => {
    api.mealPlanPreflight.and.returnValue(of(preflight));
    component.ionViewWillEnter();
    component.toggleIngredient(0, true);

    expect(component.checkedIngredientCount).toBe(1);
    expect(component.ingredientChecks).toEqual([true]);
  });

  it('uses the existing pantry-deduction completion action at finish', () => {
    api.mealPlanPreflight.and.returnValue(of(preflight));
    api.cookMealPlan.and.returnValue(of({ meal_plan: preflight.meal_plan, message: 'Done' }));
    component.ionViewWillEnter();
    component.finishWithPantryDeduction();

    expect(api.cookMealPlan).toHaveBeenCalledWith(42, '');
    expect(progress.clear).toHaveBeenCalledWith(7, 42);
    expect(router.navigate).toHaveBeenCalledWith(['/meal-details', 42], jasmine.objectContaining({ replaceUrl: true }));
  });
});
