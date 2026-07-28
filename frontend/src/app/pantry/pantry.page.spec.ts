import { ComponentFixture, TestBed } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { ExploreContainerComponentModule } from '../explore-container/explore-container.module';

import { PantryPage } from './pantry.page';

describe('PantryPage', () => {
  let component: PantryPage;
  let fixture: ComponentFixture<PantryPage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [PantryPage],
      imports: [IonicModule.forRoot(), ExploreContainerComponentModule]
    }).compileComponents();

    fixture = TestBed.createComponent(PantryPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
