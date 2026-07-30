import { Component, OnDestroy } from '@angular/core';
import { ApiService, Family, PackageItem, Recommendation, RecipeDetail, RecipeIngredient, RecipeNutrition, RecipeReview } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';

@Component({ selector: 'app-recipes', templateUrl: 'recipes.page.html', styleUrls: ['recipes.page.scss'], standalone: false })
export class RecipesPage implements OnDestroy {
  recipes: Recommendation[] = [];
  searchResults: Recommendation[] = [];
  loading = false;
  searching = false;
  searchError = '';
  message = '';
  selectedRecipe?: RecipeDetail;
  selectedRecommendation?: Recommendation;
  nutrition?: RecipeNutrition;
  servings = 2;
  favorites = new Set<number>();
  reviews: RecipeReview[] = [];
  rating = 5;
  reviewComment = '';
  householdName = '';
  families: Family[] = [];
  cookingScope: 'personal' | number = 'personal';
  searchTerm = '';
  confirmingIngredient?: RecipeIngredient;
  confirmingPackage?: PackageItem;
  packageAmount?: number;
  packageUnit = 'g';
  savingConversion = false;
  private activeFamilyId?: number;
  private searchTimer?: ReturnType<typeof setTimeout>;
  private searchSequence = 0;

  constructor(private api: ApiService, private householdContext: HouseholdContextService, private auth: AuthService) {}
  ionViewWillEnter() { this.loadRecipeRecommendations(); }
  ngOnDestroy(): void {
    if (this.searchTimer) clearTimeout(this.searchTimer);
  }

  get hasSearch(): boolean { return !!this.searchTerm.trim(); }
  get displayedRecipes(): Recommendation[] { return this.hasSearch ? this.searchResults : this.recipes; }

  onSearchChanged(value: string): void {
    const query = value.trim();
    const sequence = ++this.searchSequence;
    if (this.searchTimer) clearTimeout(this.searchTimer);
    this.searchError = '';

    if (!query) {
      this.searchResults = [];
      this.searching = false;
      return;
    }

    this.searchResults = [];
    this.searching = true;
    this.searchTimer = setTimeout(() => this.searchRecipes(query, sequence), 300);
  }

  loadRecipeRecommendations(): void {
    const userId = this.auth.user?.id;
    if (!this.api.hasToken || !userId) { this.message = 'Connect your account on Home to see recipe matches.'; return; }
    ++this.searchSequence;
    if (this.searchTimer) clearTimeout(this.searchTimer);
    this.loading = true; this.message = '';
    this.householdContext.refresh(userId).subscribe({
      next: context => {
        this.families = context.families;
        // Recipe discovery owns this choice. The app-wide context remains the
        // selected household for Home and meal planning.
        const selectedFamily = this.selectedRecipeFamily(context.families);
        this.activeFamilyId = selectedFamily?.id;
        this.householdName = selectedFamily?.name || '';
        this.api.recommendations(this.activeFamilyId).subscribe({
          next: result => {
            this.recipes = result.recommendations;
            this.loading = false;
            if (this.hasSearch) this.onSearchChanged(this.searchTerm);
          },
          error: () => { this.message = 'Could not load recipe recommendations.'; this.loading = false; },
        });
      },
      error: () => { this.message = 'Could not load your household context.'; this.loading = false; },
    });
  }
  changeCookingScope(): void {
    const family = this.cookingScope === 'personal'
      ? null
      : this.families.find(item => item.id === Number(this.cookingScope)) || null;
    this.householdName = family?.name || '';
    this.activeFamilyId = family?.id;
    this.recipes = [];
    this.searchResults = [];
    this.searchError = '';
    this.loading = true;
    this.api.recommendations(this.activeFamilyId).subscribe({
      next: result => {
        this.recipes = result.recommendations;
        this.loading = false;
        if (this.hasSearch) this.onSearchChanged(this.searchTerm);
      },
      error: () => {
        this.message = 'Could not load recipe recommendations.';
        this.loading = false;
      },
    });
  }

  get cookingScopeDescription(): string {
    return this.activeFamilyId
      ? `${this.householdName}'s shared pantry and household dietary safety rules are used.`
      : 'Only your personal pantry and your own dietary preferences are used.';
  }
  addMissingIngredientsToShoppingList(recipe: Recommendation) { this.api.addMissingToList(recipe.recipe.id, this.activeFamilyId).subscribe({ next: () => this.message = `${recipe.recipe.name}: missing ingredients added to your shopping list.`, error: () => this.message = 'Could not update the shopping list.' }); }
  openPackageConfirmation(ingredient: RecipeIngredient): void {
    const item = ingredient.package_items?.[0];
    if (!item) return;
    this.confirmingIngredient = ingredient; this.confirmingPackage = item; this.packageAmount = undefined; this.packageUnit = ingredient.unit || 'g';
  }
  closePackageConfirmation(): void { this.confirmingIngredient = undefined; this.confirmingPackage = undefined; }
  savePackageConversion(): void {
    if (!this.confirmingPackage || !this.packageAmount || this.packageAmount <= 0 || this.savingConversion) return;
    this.savingConversion = true;
    this.api.confirmPackageConversion(this.confirmingPackage.id, this.packageAmount, this.packageUnit).subscribe({
      next: result => { this.savingConversion = false; this.message = result.message; this.closePackageConfirmation(); this.loadRecipeRecommendations(); },
      error: () => { this.savingConversion = false; this.message = 'Could not save that package conversion.'; },
    });
  }
  showRecipeDetails(recipe: Recommendation) { this.selectedRecommendation = recipe; this.nutrition = undefined; this.servings = recipe.recipe.servings || 2; this.api.recipe(recipe.recipe.id).subscribe({ next: detail => { this.selectedRecipe = detail; this.loadReviews(detail.id); this.api.recipeNutrition(detail.id).subscribe({ next: nutrition => this.nutrition = nutrition, error: () => this.nutrition = undefined }); }, error: () => this.message = 'Could not load this recipe.' }); }
  closeRecipeDetails() { this.selectedRecipe = undefined; this.selectedRecommendation = undefined; this.nutrition = undefined; this.reviews = []; this.reviewComment = ''; }
  ingredientQuantity(quantity?: string): string { const value = Number(quantity); if (!quantity || Number.isNaN(value)) return quantity || ''; const base = this.selectedRecipe?.servings || this.selectedRecommendation?.recipe.servings || 2; return String(Math.round(value * this.servings / base * 100) / 100); }
  toggleFavorite(recipe: Recommendation): void { const id = recipe.recipe.id; const request = this.favorites.has(id) ? this.api.unfavoriteRecipe(id) : this.api.favoriteRecipe(id); request.subscribe({ next: () => this.favorites.has(id) ? this.favorites.delete(id) : this.favorites.add(id), error: () => this.message = 'Could not update favorites.' }); }
  submitReview(): void { if (!this.selectedRecipe) return; this.api.reviewRecipe(this.selectedRecipe.id, this.rating, this.reviewComment).subscribe({ next: () => { this.reviewComment = ''; this.loadReviews(this.selectedRecipe!.id); }, error: () => this.message = 'Could not save your rating.' }); }
  private loadReviews(recipeId: number): void { this.api.recipeReviews(recipeId).subscribe({ next: response => this.reviews = Array.isArray(response) ? response : response.reviews || [], error: () => this.reviews = [] }); }
  private searchRecipes(query: string, sequence: number): void {
    this.api.searchRecipes(query, this.activeFamilyId).subscribe({
      next: response => {
        if (sequence !== this.searchSequence) return;
        this.searchResults = response.data;
        this.searching = false;
      },
      error: () => {
        if (sequence !== this.searchSequence) return;
        this.searchResults = [];
        this.searchError = 'Could not search menus right now. Check your connection and try again.';
        this.searching = false;
      },
    });
  }

  private selectedRecipeFamily(families: Family[]): Family | undefined {
    return this.cookingScope === 'personal'
      ? undefined
      : families.find(family => family.id === Number(this.cookingScope));
  }
}
