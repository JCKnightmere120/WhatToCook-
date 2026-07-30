import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MealPlanPage } from './meal-plan.page';
const routes: Routes = [{ path: '', component: MealPlanPage }];
@NgModule({ imports: [RouterModule.forChild(routes)], exports: [RouterModule] })
export class MealPlanPageRoutingModule {}
