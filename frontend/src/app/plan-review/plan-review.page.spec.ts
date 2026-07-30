import { ActivatedRoute, Router } from '@angular/router';
import { of } from 'rxjs';
import {
  ApiService,
  HouseholdProfile,
  MealPlan,
  MealPlanBatchResponse,
  MealPlanIngredientStatus,
} from '../services/api.service';
import { PlanReviewPage } from './plan-review.page';

describe('PlanReviewPage', () => {
  let api: jasmine.SpyObj<ApiService>;
  let router: jasmine.SpyObj<Router>;
  let component: PlanReviewPage;

  const diner: HouseholdProfile = { id: 31, family_id: 7, name: 'Dad', relation: 'Father' };
  const meal: MealPlan = {
    id: 81,
    recipe_id: 14,
    family_id: 7,
    planned_date: '2099-08-03',
    meal_type: 'dinner',
    status: 'draft',
    servings: 2,
    diner_profile_ids: [diner.id],
    recipe: { id: 14, name: 'Adobong Manok', ingredients: [] },
  };
  const shortage: MealPlanIngredientStatus = {
    name: 'Chicken',
    unit: 'kg',
    required_quantity: 1,
    pantry_quantity: 0.25,
    missing_quantity: 0.75,
    status: 'low_stock',
  };

  const response = (status: 'draft' | 'saved' = 'draft'): MealPlanBatchResponse => ({
    batch: { id: 17, user_id: 1, family_id: 7, start_date: '2099-08-03', end_date: '2099-08-09', status },
    meal_plans: [meal],
    summary: { meal_count: 1, ready_count: 0, ingredients: [shortage], shortages: [shortage], needs_review: [] },
    conflicts: [],
  });

  beforeEach(() => {
    api = jasmine.createSpyObj<ApiService>('ApiService', [
      'updateMealPlanBatchMeal',
      'addMealPlanBatchShortagesToShoppingList',
      'addMealPlanBatchPurchasedItems',
      'saveMealPlanBatch',
    ]);
    router = jasmine.createSpyObj<Router>('Router', ['navigate', 'navigateByUrl']);
    const route = { snapshot: { paramMap: { get: () => '17' } } } as unknown as ActivatedRoute;
    component = new PlanReviewPage(api, route, router);
    component.batchId = 17;
    component.response = response();
    component.diners = [diner];
  });

  it('updates one draft meal with its date, recipe, diners, and servings', () => {
    api.updateMealPlanBatchMeal.and.returnValue(of(response()));
    component.openMealEditor(meal);
    component.editor!.recipeId = 15;
    component.editor!.plannedDate = '2099-08-04';
    component.editor!.servings = 3;

    component.saveMealChange();

    expect(api.updateMealPlanBatchMeal).toHaveBeenCalledWith(17, meal.id, {
      recipe_id: 15,
      planned_date: '2099-08-04',
      meal_type: 'dinner',
      servings: 3,
      diner_profile_ids: [diner.id],
    });
    expect(component.editor).toBeUndefined();
  });

  it('adds confirmed bought shortages to the pantry and uses the returned recheck', () => {
    const refreshed = response();
    refreshed.summary = { meal_count: 1, ready_count: 1, ingredients: [], shortages: [], needs_review: [] };
    api.addMealPlanBatchPurchasedItems.and.returnValue(of({ items: [], preview: refreshed }));

    component.openPurchasedItems();
    component.savePurchasedItems();

    expect(api.addMealPlanBatchPurchasedItems).toHaveBeenCalledWith(17, [{
      name: 'Chicken', quantity: 0.75, unit: 'kg', storage_type: 'unknown',
    }]);
    expect(component.purchaseDrafts).toBeUndefined();
    expect(component.response?.summary.ready_count).toBe(1);
    expect(component.message).toContain('Ingredient availability has been checked again');
  });

  it('saves with the selected conflict resolution and returns to the planner', () => {
    component.replaceConflicts = true;
    api.saveMealPlanBatch.and.returnValue(of(response('saved')));

    component.savePlan();

    expect(api.saveMealPlanBatch).toHaveBeenCalledWith(17, { conflict_action: 'replace_conflicting' });
    expect(router.navigateByUrl).toHaveBeenCalledWith('/tabs/meal-plan', { replaceUrl: true });
  });

  it('adds shortages to the shopping list only once during a review', () => {
    api.addMealPlanBatchShortagesToShoppingList.and.returnValue(of({ items: [], message: 'Added.' }));

    component.addShortagesToList();
    component.addShortagesToList();

    expect(api.addMealPlanBatchShortagesToShoppingList).toHaveBeenCalledTimes(1);
    expect(component.shortagesAdded).toBeTrue();
    expect(component.message).toBe('Added.');
  });
});
