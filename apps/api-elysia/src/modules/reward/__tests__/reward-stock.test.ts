import { describe, expect, test } from 'bun:test';

import {
  filterInStockBranchStocks,
  getAvailableStock,
  hasAvailableStock,
  sumAvailableStock,
} from '../lib/reward-stock';

describe('reward-stock helpers', () => {
  test('getAvailableStock subtracts heldStock and floors at 0', () => {
    expect(getAvailableStock({ actualStock: 5, heldStock: 0 })).toBe(5);
    expect(getAvailableStock({ actualStock: 5, heldStock: 3 })).toBe(2);
    expect(getAvailableStock({ actualStock: 5, heldStock: 5 })).toBe(0);
    expect(getAvailableStock({ actualStock: 5, heldStock: 8 })).toBe(0);
    expect(getAvailableStock({ actualStock: 0, heldStock: 0 })).toBe(0);
  });

  test('sumAvailableStock totals all branches when branchId omitted', () => {
    const stocks = [
      { branchId: 1, actualStock: 3, heldStock: 1 },
      { branchId: 2, actualStock: 4, heldStock: 4 },
      { branchId: 3, actualStock: 2, heldStock: 0 },
    ];
    expect(sumAvailableStock(stocks)).toBe(4);
  });

  test('sumAvailableStock scopes to branchId when provided', () => {
    const stocks = [
      { branchId: 1, actualStock: 10, heldStock: 0 },
      { branchId: 2, actualStock: 10, heldStock: 0 },
    ];
    expect(sumAvailableStock(stocks, 2)).toBe(10);
    expect(sumAvailableStock(stocks, 99)).toBe(0);
  });

  test('hasAvailableStock without branchId requires any branch in stock', () => {
    expect(
      hasAvailableStock([{ branchId: 1, actualStock: 0, heldStock: 0 }]),
    ).toBe(false);
    expect(
      hasAvailableStock([{ branchId: 1, actualStock: 5, heldStock: 5 }]),
    ).toBe(false);
    expect(
      hasAvailableStock([
        { branchId: 1, actualStock: 0, heldStock: 0 },
        { branchId: 2, actualStock: 1, heldStock: 0 },
      ]),
    ).toBe(true);
    expect(hasAvailableStock([])).toBe(false);
  });

  test('hasAvailableStock with branchId ignores other branches', () => {
    const stocks = [
      { branchId: 1, actualStock: 10, heldStock: 0 },
      { branchId: 2, actualStock: 10, heldStock: 10 },
    ];
    expect(hasAvailableStock(stocks, 1)).toBe(true);
    expect(hasAvailableStock(stocks, 2)).toBe(false);
    expect(hasAvailableStock(stocks, 3)).toBe(false);
  });

  test('filterInStockBranchStocks drops zero-available rows', () => {
    const stocks = [
      { branchId: 1, actualStock: 2, heldStock: 0 },
      { branchId: 2, actualStock: 1, heldStock: 1 },
      { branchId: 3, actualStock: 0, heldStock: 0 },
    ];
    expect(filterInStockBranchStocks(stocks).map((s) => s.branchId)).toEqual([1]);
  });
});
