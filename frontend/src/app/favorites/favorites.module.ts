import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { FavoritesPageRoutingModule } from './favorites-routing.module';
import { FavoritesPage } from './favorites.page';
@NgModule({ imports: [CommonModule, IonicModule, FavoritesPageRoutingModule], declarations: [FavoritesPage] }) export class FavoritesPageModule {}
