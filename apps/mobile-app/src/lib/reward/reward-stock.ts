export function getAvailableBranchStock(stock: {
  actualStock: number;
  heldStock: number;
}): number {
  return Math.max(stock.actualStock - stock.heldStock, 0);
}
