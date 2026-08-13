import { Component } from '@angular/core';

@Component({
  selector: 'app-tabs',
  templateUrl: 'tabs.page.html',
  styleUrls: ['tabs.page.scss'],
  standalone: false,
})
export class TabsPage {
  private readonly tabTitles: Record<string, string> = {
    dashboard: 'Home',
    pantry: 'Pantry',
    'meal-plan': 'Meal plan',
    recipes: 'Recipes',
    more: 'More',
  };
  activeTabTitle = this.tabTitles['dashboard'];

  constructor() {}

  onTabChanged(event: { tab: string }): void {
    const tab = event?.tab;
    if (tab && this.tabTitles[tab]) this.activeTabTitle = this.tabTitles[tab];
  }

}
