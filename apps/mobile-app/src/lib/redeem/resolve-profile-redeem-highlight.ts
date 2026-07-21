import type { ActiveRedeemItem } from '@/types/active-redeem';
import type { RedeemHistoryItem } from '@/types/redeem';
import type { RewardCatalogItem } from '@/types/reward';

import { mapActiveRedeemToReward } from '@/lib/active-redeem/map-active-redeem-reward';

export type ProfileRedeemHighlight =
  | {
      kind: 'pending';
      title: string;
      reward: RewardCatalogItem;
      activeRedeem: ActiveRedeemItem;
      href: '/card/redeem-qr';
    }
  | {
      kind: 'completed';
      title: string;
      reward: RewardCatalogItem;
      invoiceId: string;
      href: { pathname: '/redeem/[id]'; params: { id: string } };
    }
  | {
      kind: 'empty';
    };

function mapHistoryToReward(item: RedeemHistoryItem): RewardCatalogItem {
  return {
    id: item.reward.id,
    sku: item.reward.sku,
    name: item.reward.name,
    categoryId: 0,
    categoryName: item.branch.name,
    categorySlug: '',
    pointsRequired: item.pointsRedeemed,
    stockRemaining: 0,
    image: item.reward.imageUrl,
  };
}

export function resolveProfileRedeemHighlight(input: {
  activeRedeem: ActiveRedeemItem | null | undefined;
  latestHistoryItem: RedeemHistoryItem | undefined;
}): ProfileRedeemHighlight {
  if (input.activeRedeem) {
    return {
      kind: 'pending',
      title: 'Hadiah sedang diklaim',
      reward: mapActiveRedeemToReward(input.activeRedeem),
      activeRedeem: input.activeRedeem,
      href: '/card/redeem-qr',
    };
  }

  const latest = input.latestHistoryItem;
  if (latest && latest.status === 'selesai') {
    return {
      kind: 'completed',
      title: 'Reward terakhir diklaim',
      reward: mapHistoryToReward(latest),
      invoiceId: latest.id,
      href: { pathname: '/redeem/[id]', params: { id: latest.id } },
    };
  }

  return { kind: 'empty' };
}
