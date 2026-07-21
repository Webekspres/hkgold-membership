export type BranchStockLike = {
  actualStock: number;
  heldStock: number;
  branchId?: number;
};

/** available = max(actualStock - heldStock, 0) */
export function getAvailableStock(stock: Pick<BranchStockLike, 'actualStock' | 'heldStock'>): number {
  return Math.max(stock.actualStock - stock.heldStock, 0);
}

export function sumAvailableStock(stocks: BranchStockLike[], branchId?: number): number {
  return stocks.reduce((total, stock) => {
    if (branchId != null && stock.branchId !== branchId) {
      return total;
    }
    return total + getAvailableStock(stock);
  }, 0);
}

export function hasAvailableStock(
  stocks: Array<BranchStockLike & { branchId: number }>,
  branchId?: number,
): boolean {
  if (branchId != null) {
    const branchStock = stocks.find((stock) => stock.branchId === branchId);
    return branchStock ? getAvailableStock(branchStock) > 0 : false;
  }
  return sumAvailableStock(stocks) > 0;
}

export function filterInStockBranchStocks<T extends BranchStockLike>(stocks: T[]): T[] {
  return stocks.filter((stock) => getAvailableStock(stock) > 0);
}
