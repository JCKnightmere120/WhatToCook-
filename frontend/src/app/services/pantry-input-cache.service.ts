import { Injectable } from '@angular/core';
import { PantryInputResult } from './api.service';

const PREFIX = 'whattocook_barcode_v1_';
const TTL = 24 * 60 * 60 * 1000;
interface CacheEntry { savedAt: number; result: PantryInputResult; }

@Injectable({ providedIn: 'root' })
export class PantryInputCacheService {
  getBarcode(barcode: string): PantryInputResult | undefined {
    try {
      const entry = JSON.parse(localStorage.getItem(PREFIX + barcode) || 'null') as CacheEntry | null;
      if (!entry || Date.now() - entry.savedAt > TTL) { if (entry) localStorage.removeItem(PREFIX + barcode); return undefined; }
      return entry.result;
    } catch { return undefined; }
  }
  putBarcode(barcode: string, result: PantryInputResult): void {
    try { localStorage.setItem(PREFIX + barcode, JSON.stringify({ savedAt: Date.now(), result })); } catch { /* Cache is optional. */ }
  }
}
