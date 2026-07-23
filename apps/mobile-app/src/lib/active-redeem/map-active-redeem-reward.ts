import type { ActiveRedeemItem } from '@/types/active-redeem';
import type { RewardCatalogItem } from '@/types/reward';

export function mapActiveRedeemToReward(activeRedeem: ActiveRedeemItem): RewardCatalogItem {
  return {
    id: activeRedeem.reward.id,
    sku: activeRedeem.reward.sku,
    name: activeRedeem.reward.name,
    categoryId: 0,
    categoryName: activeRedeem.branch.name,
    categorySlug: '',
    pointsRequired: activeRedeem.heldPoints,
    stockRemaining: 0,
    image: activeRedeem.reward.imageUrl,
  };
}
