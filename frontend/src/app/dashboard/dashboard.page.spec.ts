import { ComponentFixture, TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { IonicModule } from '@ionic/angular';

import { ExploreContainerComponentModule } from '../explore-container/explore-container.module';

import { DashboardPage } from './dashboard.page';
import { of } from 'rxjs';
import { ApiService, Family } from '../services/api.service';
import { HouseholdContextService } from '../services/household-context.service';
import { AuthService } from '../services/auth.service';

describe('DashboardPage', () => {
  let component: DashboardPage;
  let fixture: ComponentFixture<DashboardPage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [DashboardPage],
      imports: [IonicModule.forRoot(), ExploreContainerComponentModule, HttpClientTestingModule]
    }).compileComponents();

    fixture = TestBed.createComponent(DashboardPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('loads personal stock alongside the active household stock', () => {
    const family: Family = { id: 7, name: 'Santos', owner_id: 1 };
    const api = jasmine.createSpyObj<ApiService>('ApiService', ['pantry', 'recommendations']);
    Object.defineProperty(api, 'hasToken', { value: true });
    api.pantry.and.callFake((familyId?: number, personal = false) => of(personal ? [{ id: 1, name: 'Salt' }] : [{ id: 2, name: 'Rice', family_id: familyId }]));
    api.recommendations.and.returnValue(of({ recommendations: [] }));
    const context = jasmine.createSpyObj<HouseholdContextService>('HouseholdContextService', ['refresh']);
    context.refresh.and.returnValue(of({ userId: 1, families: [family], activeFamily: family }));
    const page = new DashboardPage(api, context, { user: { id: 1 } } as AuthService);

    page.loadDashboardData();

    expect(api.pantry).toHaveBeenCalledWith(undefined, true);
    expect(api.pantry).toHaveBeenCalledWith(7);
    expect(page.personalItems.map(item => item.name)).toEqual(['Salt']);
    expect(page.householdItems.map(item => item.name)).toEqual(['Rice']);
  });
});
