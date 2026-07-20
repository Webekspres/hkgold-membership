import { describe, expect, test } from 'bun:test';

import { getAvailableBranchStock } from './reward-stock';

describe('getAvailableBranchStock', () => {
  test('returns available units and never negative', () => {
    expect(getAvailableBranchStock({ actualStock: 4, heldStock: 1 })).toBe(3);
    expect(getAvailableBranchStock({ actualStock: 2, heldStock: 2 })).toBe(0);
    expect(getAvailableBranchStock({ actualStock: 1, heldStock: 5 })).toBe(0);
  });
});
