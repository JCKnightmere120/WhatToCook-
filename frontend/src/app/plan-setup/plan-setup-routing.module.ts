import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { PlanSetupPage } from './plan-setup.page';

const routes: Routes = [{ path: '', component: PlanSetupPage }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PlanSetupPageRoutingModule {}
