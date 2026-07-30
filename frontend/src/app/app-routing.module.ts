import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';
import { AuthGuard } from './guards/auth.guard';

const routes: Routes = [
  {
    path: 'auth',
    loadChildren: () => import('./auth/auth.module').then(m => m.AuthPageModule)
  },
  {
    path: 'onboarding',
    canActivate: [AuthGuard],
    loadChildren: () => import('./onboarding/onboarding.module').then(m => m.OnboardingPageModule)
  },
  {
    path: 'family',
    canActivate: [AuthGuard],
    loadChildren: () => import('./family/family.module').then(m => m.FamilyPageModule)
  },
  {
    path: 'profile',
    canActivate: [AuthGuard],
    loadChildren: () => import('./profile/profile.module').then(m => m.ProfilePageModule)
  },
  {
    path: 'settings',
    canActivate: [AuthGuard],
    loadChildren: () => import('./settings/settings.module').then(m => m.SettingsPageModule)
  },
  { path: 'shopping-list', canActivate: [AuthGuard], loadChildren: () => import('./shopping-list/shopping-list.module').then(m => m.ShoppingListPageModule) },
  { path: 'meal-history', canActivate: [AuthGuard], loadChildren: () => import('./meal-history/meal-history.module').then(m => m.MealHistoryPageModule) },
  { path: 'plan-setup', canActivate: [AuthGuard], loadChildren: () => import('./plan-setup/plan-setup.module').then(m => m.PlanSetupPageModule) },
  { path: 'plan-review/:id', canActivate: [AuthGuard], loadChildren: () => import('./plan-review/plan-review.module').then(m => m.PlanReviewPageModule) },
  { path: 'meal-details/:id', canActivate: [AuthGuard], loadChildren: () => import('./meal-details/meal-details.module').then(m => m.MealDetailsPageModule) },
  {
    path: '',
    canActivate: [AuthGuard],
    loadChildren: () => import('./tabs/tabs.module').then(m => m.TabsPageModule)
  }
];
@NgModule({
  imports: [
    RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules })
  ],
  exports: [RouterModule]
})
export class AppRoutingModule {}
