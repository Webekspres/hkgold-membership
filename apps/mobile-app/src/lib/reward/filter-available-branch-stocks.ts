import { getAvailableBranchStock } from './reward-stock';
import type { RewardBranchStockItem } from '@/types/reward';

export function filterAvailableBranchStocks(
  stocks: RewardBranchStockItem[],
): RewardBranchStockItem[] {
  return stocks.filter((stock) => getAvailableBranchStock(stock) > 0);
}
