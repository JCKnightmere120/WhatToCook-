import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { MealPlanPageRoutingModule } from './meal-plan-routing.module';
import { MealPlanPage } from './meal-plan.page';
@NgModule({ imports: [CommonModule, IonicModule, MealPlanPageRoutingModule], declarations: [MealPlanPage] })
export class MealPlanPageModule {}
