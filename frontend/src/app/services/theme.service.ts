import { DOCUMENT } from '@angular/common';
import { Inject, Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class ThemeService {
  private readonly key = 'whattocook_dark_mode';
  dark = false;
  constructor(@Inject(DOCUMENT) private document: Document) {}
  initialise(): void { this.setDark(localStorage.getItem(this.key) === 'true'); }
  setDark(enabled: boolean): void { this.dark = enabled; this.document.body.classList.toggle('ion-palette-dark', enabled); localStorage.setItem(this.key, String(enabled)); }
}
