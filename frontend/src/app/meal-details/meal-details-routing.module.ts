import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MealDetailsPage } from './meal-details.page';

const routes: Routes = [{ path: '', component: MealDetailsPage }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class MealDetailsPageRoutingModule {}
