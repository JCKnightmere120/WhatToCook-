import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TabsPage } from './tabs.page';

describe('TabsPage', () => {
  let component: TabsPage;
  let fixture: ComponentFixture<TabsPage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [TabsPage],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    }).compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TabsPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('renders the five primary destinations with a labelled shell header', () => {
    const buttons = Array.from(fixture.nativeElement.querySelectorAll('ion-tab-button')) as HTMLElement[];

    expect(fixture.nativeElement.querySelector('.wtc-shell-header__brand')?.textContent).toContain('WhatToCook');
    expect(fixture.nativeElement.querySelector('.wtc-shell-header__page')?.textContent).toContain('Home');
    expect(buttons.map(button => button.getAttribute('href'))).toEqual([
      '/tabs/dashboard',
      '/tabs/pantry',
      '/tabs/meal-plan',
      '/tabs/recipes',
      '/tabs/more',
    ]);
    expect(buttons.map(button => button.textContent?.trim())).toEqual(['Home', 'Pantry', 'Plan', 'Recipes', 'More']);
  });

  it('updates the shell title when a tab changes', () => {
    component.onTabChanged({ tab: 'meal-plan' });
    fixture.detectChanges();

    expect(component.activeTabTitle).toBe('Meal plan');
    expect(fixture.nativeElement.querySelector('.wtc-shell-header__page')?.textContent).toContain('Meal plan');
  });
});
