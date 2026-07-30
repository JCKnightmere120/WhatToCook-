import { PantryInputCacheService } from './pantry-input-cache.service';

describe('PantryInputCacheService', () => {
  const result = { source: 'barcode' as const, needs_review: true, message: 'Review', candidates: [{ name: 'Tuna' }] };

  beforeEach(() => localStorage.clear());

  it('returns a cached barcode result', () => {
    const cache = new PantryInputCacheService();
    cache.putBarcode('4800012345678', result);
    expect(cache.getBarcode('4800012345678')).toEqual(result);
  });

  it('does not return an expired barcode result', () => {
    spyOn(Date, 'now').and.returnValue(1);
    const cache = new PantryInputCacheService();
    cache.putBarcode('4800012345678', result);
    (Date.now as jasmine.Spy).and.returnValue(24 * 60 * 60 * 1000 + 2);
    expect(cache.getBarcode('4800012345678')).toBeUndefined();
  });
});
