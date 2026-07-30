import { Component, Input } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { IonicModule, ModalController } from '@ionic/angular';
import { PantryItem } from '../services/api.service';

@Component({
  selector: 'app-use-amount-modal',
  standalone: true,
  imports: [FormsModule, IonicModule],
  styles: [`
    .available { margin: 0 0 16px; color: var(--ion-color-medium); }
    .stepper { display: flex; align-items: center; gap: 8px; width: 100%; margin-top: 6px; }
    .stepper ion-input { flex: 1; text-align: center; --padding-start: 8px; }
    .stepper ion-button { margin: 0; min-width: 42px; }
    .remainder { margin: 14px 0; border-radius: 12px; --background: var(--ion-color-light); }
    .remainder h2 { margin: 2px 0 0; color: var(--ion-color-success-shade); }
    .actions { display: grid; grid-template-columns: 1fr 1fr 1.2fr; gap: 8px; margin-top: 22px; }
    .actions ion-button { margin: 0; }
  `],
  template: `
    <ion-header><ion-toolbar><ion-title>Use {{ item.name }}</ion-title><ion-buttons slot="end"><ion-button (click)="cancel()">Cancel</ion-button></ion-buttons></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <p class="available">Available: <strong>{{ available }} {{ item.unit }}</strong></p>
      <ion-item lines="none"><ion-label position="stacked">Amount to use ({{ item.unit }})</ion-label><div class="stepper"><ion-button fill="outline" (click)="adjust(-1)">−</ion-button><ion-input type="number" inputmode="decimal" min="0.001" [max]="available" step="0.1" [(ngModel)]="amount"></ion-input><ion-button fill="outline" (click)="adjust(1)">+</ion-button></div></ion-item>
      <ion-item lines="none" class="remainder"><ion-label><p>Remaining after use</p><h2>{{ remainder }} {{ item.unit }}</h2></ion-label></ion-item>
      <ion-textarea label="Reason (optional)" labelPlacement="stacked" placeholder="e.g. Dinner prep" [(ngModel)]="reason"></ion-textarea>
      <div class="actions"><ion-button fill="outline" (click)="useAll()">Use all</ion-button><ion-button fill="clear" (click)="cancel()">Cancel</ion-button><ion-button (click)="confirm()" [disabled]="amount <= 0 || amount > available">Confirm use</ion-button></div>
    </ion-content>
  `,
})
export class UseAmountModalComponent {
  @Input() item!: PantryItem;
  amount = 0;
  reason = '';

  constructor(private modalController: ModalController) {}

  get available(): number { return Number(this.item.quantity_value ?? this.item.quantity ?? 0) || 0; }
  get remainder(): number { return Math.max(0, Number((this.available - (Number(this.amount) || 0)).toFixed(3))); }
  ionViewWillEnter(): void { this.amount = this.available; }
  adjust(change: number): void { this.amount = Math.max(0, Math.min(this.available, Number((this.amount + change).toFixed(3)))); }
  useAll(): void { this.amount = this.available; }
  cancel(): Promise<boolean> { return this.modalController.dismiss(); }
  confirm(): Promise<boolean> { return this.modalController.dismiss({ amount: Number(this.amount), reason: this.reason.trim() }, 'confirm'); }
}
