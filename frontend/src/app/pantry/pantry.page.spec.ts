import { ComponentFixture, TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { FormsModule } from '@angular/forms';
import { RouterTestingModule } from '@angular/router/testing';
import { IonicModule } from '@ionic/angular';

import { ExploreContainerComponentModule } from '../explore-container/explore-container.module';

import { PantryPage } from './pantry.page';
import { receiptCandidates } from './pantry.page';
import { UseAmountModalComponent } from './use-amount-modal.component';

describe('PantryPage', () => {
  let component: PantryPage;
  let fixture: ComponentFixture<PantryPage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [PantryPage],
      imports: [IonicModule.forRoot(), ExploreContainerComponentModule, FormsModule, HttpClientTestingModule, RouterTestingModule]
    }).compileComponents();

    fixture = TestBed.createComponent(PantryPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

describe('receiptCandidates', () => {
  it('creates editable candidates and excludes totals', () => {
    expect(receiptCandidates('2 Tuna 120.00\nCoconut Milk 45.00\nTOTAL 285.00')).toEqual([
      jasmine.objectContaining({ name: 'Tuna', quantity: '2' }),
      jasmine.objectContaining({ name: 'Coconut Milk', quantity: '1' }),
    ]);
  });
});

describe('UseAmountModalComponent', () => {
  it('locks the pantry unit and previews the remainder', () => {
    const modal = new UseAmountModalComponent(jasmine.createSpyObj('ModalController', ['dismiss']));
    modal.item = { id: 1, name: 'Rice', quantity_value: 3, unit: 'kg' };
    modal.ionViewWillEnter();
    modal.amount = 1.25;

    expect(modal.available).toBe(3);
    expect(modal.remainder).toBe(1.75);
    expect(modal.item.unit).toBe('kg');
  });
});
