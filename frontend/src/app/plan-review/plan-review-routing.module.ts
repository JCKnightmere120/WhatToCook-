import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { PlanReviewPage } from './plan-review.page';

const routes: Routes = [{ path: '', component: PlanReviewPage }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PlanReviewPageRoutingModule {}
