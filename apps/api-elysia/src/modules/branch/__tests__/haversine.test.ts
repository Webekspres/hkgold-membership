import { describe, test, expect } from 'bun:test';
import { haversineKm } from '../lib/haversine';

describe('haversineKm', () => {
  test('same point → 0', () => {
    expect(haversineKm(-6.2, 106.8, -6.2, 106.8)).toBe(0);
  });

  test('Jakarta → Bandung roughly 115–130 km', () => {
    const km = haversineKm(-6.2088, 106.8456, -6.9175, 107.6191);
    expect(km).toBeGreaterThan(100);
    expect(km).toBeLessThan(150);
  });
});
