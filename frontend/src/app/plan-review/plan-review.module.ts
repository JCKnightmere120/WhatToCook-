import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';
import { PlanReviewPageRoutingModule } from './plan-review-routing.module';
import { PlanReviewPage } from './plan-review.page';

@NgModule({
  imports: [CommonModule, FormsModule, IonicModule, PlanReviewPageRoutingModule],
  declarations: [PlanReviewPage],
})
export class PlanReviewPageModule {}
