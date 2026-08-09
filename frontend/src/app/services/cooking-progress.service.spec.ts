import { CookingProgressService } from './cooking-progress.service';

describe('CookingProgressService', () => {
  let service: CookingProgressService;

  beforeEach(() => { localStorage.clear(); service = new CookingProgressService(); });

  it('keeps cooking progress separate for each user and meal plan', () => {
    service.save(1, 42, 3);
    service.save(2, 42, 1);

    expect(service.load(1, 42, 5).stepIndex).toBe(3);
    expect(service.load(2, 42, 5).stepIndex).toBe(1);
    expect(service.load(1, 99, 5).stepIndex).toBe(0);
  });

  it('clamps stale saved progress when a recipe has fewer steps', () => {
    service.save(1, 42, 8);

    expect(service.load(1, 42, 3).stepIndex).toBe(2);
  });
});
