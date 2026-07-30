import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { MorePageRoutingModule } from './more-routing.module';
import { MorePage } from './more.page';
@NgModule({ imports: [CommonModule, IonicModule, MorePageRoutingModule], declarations: [MorePage] })
export class MorePageModule {}
