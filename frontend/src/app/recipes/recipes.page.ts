import { Component } from '@angular/core';
import { ApiService, Recommendation, RecipeDetail } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';

@Component({ selector: 'app-recipes', templateUrl: 'recipes.page.html', styleUrls: ['recipes.page.scss'], standalone: false })
export class RecipesPage {
  recipes: Recommendation[] = [];
  loading = false;
  message = '';
  selectedRecipe?: RecipeDetail;
  householdName = '';
  constructor(private api: ApiService, private householdContext: HouseholdContextService, private auth: AuthService) {}
  ionViewWillEnter() { this.loadRecipeRecommendations(); }
  loadRecipeRecommendations(): void {
    const userId = this.auth.user?.id;
    if (!this.api.hasToken || !userId) { this.message = 'Connect your account on Home to see recipe matches.'; return; }
    this.loading = true; this.message = '';
    this.householdContext.refresh(userId).subscribe({
      next: context => {
        this.householdName = context.activeFamily?.name || '';
        this.api.recommendations(context.activeFamily?.id).subscribe({
          next: result => { this.recipes = result.recommendations; this.loading = false; },
          error: () => { this.message = 'Could not load recipe recommendations.'; this.loading = false; },
        });
      },
      error: () => { this.message = 'Could not load your household context.'; this.loading = false; },
    });
  }
  addMissingIngredientsToShoppingList(recipe: Recommendation) { this.api.addMissingToList(recipe.recipe.id).subscribe({ next: () => this.message = `${recipe.recipe.name}: missing ingredients added to your shopping list.`, error: () => this.message = 'Could not update the shopping list.' }); }
  showRecipeDetails(recipe: Recommendation) { this.api.recipe(recipe.recipe.id).subscribe({ next: detail => this.selectedRecipe = detail, error: () => this.message = 'Could not load this recipe.' }); }
  closeRecipeDetails() { this.selectedRecipe = undefined; }
}
