import { ComponentFixture, TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { FormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';

import { ExploreContainerComponentModule } from '../explore-container/explore-container.module';

import { RecipesPage } from './recipes.page';
import { of } from 'rxjs';
import { ApiService, Family } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { HouseholdContextService } from '../services/household-context.service';

describe('RecipesPage', () => {
  let component: RecipesPage;
  let fixture: ComponentFixture<RecipesPage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [RecipesPage],
      imports: [IonicModule.forRoot(), ExploreContainerComponentModule, FormsModule, HttpClientTestingModule]
    }).compileComponents();

    fixture = TestBed.createComponent(RecipesPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('keeps a personal recipe scope local instead of selecting global personal context', () => {
    const family: Family = { id: 9, name: 'Santos', owner_id: 1 };
    const api = jasmine.createSpyObj<ApiService>('ApiService', ['recommendations']);
    api.recommendations.and.returnValue(of({ recommendations: [] }));
    Object.defineProperty(api, 'hasToken', { value: true });
    const context = jasmine.createSpyObj<HouseholdContextService>('HouseholdContextService', ['select']);
    const page = new RecipesPage(api, context, { user: { id: 1 } } as AuthService);
    page.families = [family];
    page.cookingScope = 'personal';

    page.changeCookingScope();

    expect(context.select).not.toHaveBeenCalled();
    expect(api.recommendations).toHaveBeenCalledWith(undefined);
  });
});
