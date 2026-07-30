import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';
import { PlanSetupPageRoutingModule } from './plan-setup-routing.module';
import { PlanSetupPage } from './plan-setup.page';

@NgModule({
  imports: [CommonModule, FormsModule, IonicModule, PlanSetupPageRoutingModule],
  declarations: [PlanSetupPage],
})
export class PlanSetupPageModule {}
