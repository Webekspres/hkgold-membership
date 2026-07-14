import type { ActiveRedeemItem } from '@/types/active-redeem';
import type { RewardCatalogItem } from '@/types/reward';

export function mapActiveRedeemToReward(activeRedeem: ActiveRedeemItem): RewardCatalogItem {
  return {
    id: activeRedeem.redeemId,
    sku: activeRedeem.sku,
    name: activeRedeem.name,
    categoryId: 0,
    categoryName: activeRedeem.categoryName,
    categorySlug: '',
    pointsRequired: activeRedeem.pointsRequired,
    stockRemaining: 0,
    image: activeRedeem.image,
  };
}
