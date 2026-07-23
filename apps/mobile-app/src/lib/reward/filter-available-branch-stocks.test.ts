import { describe, expect, test } from 'bun:test';

import { filterAvailableBranchStocks } from './filter-available-branch-stocks';
import type { RewardBranchStockItem } from '@/types/reward';

function stock(
  branchId: number,
  actualStock: number,
  heldStock: number,
): RewardBranchStockItem {
  return {
    branchId,
    branchName: `Cabang ${branchId}`,
    subdistrict: 'Kel',
    city: 'Kota',
    locationUrl: null,
    actualStock,
    heldStock,
  };
}

describe('filterAvailableBranchStocks', () => {
  test('keeps branches with available stock only', () => {
    const branches = [
      stock(1, 5, 0),
      stock(2, 3, 3),
      stock(3, 0, 0),
      stock(4, 2, 5),
    ];

    expect(filterAvailableBranchStocks(branches).map((item) => item.branchId)).toEqual([1]);
  });

  test('returns empty array when all branches are out of stock', () => {
    expect(filterAvailableBranchStocks([stock(1, 2, 2), stock(2, 0, 0)])).toEqual([]);
  });
});
