import { getRedeemHistoryById } from '@/mocks/mock-redeem-history';
import type { ActiveRedeemItem } from '@/types/active-redeem';

const ACTIVE_REDEEM_DURATION_MS = 75 * 60 * 1000;
const MOCK_REDEEM_TOKEN = 'RDM-8F3K2Q';

function buildActiveRedeem(): ActiveRedeemItem {
  const redeem = getRedeemHistoryById('redeem-3');

  if (!redeem) {
    throw new Error('Mock redeem-3 is required for active redeem state.');
  }

  return {
    redeemId: redeem.id,
    redeemToken: MOCK_REDEEM_TOKEN,
    expiresAt: new Date(Date.now() + ACTIVE_REDEEM_DURATION_MS).toISOString(),
    sku: redeem.sku,
    name: redeem.name,
    categoryName: redeem.categoryName,
    image: redeem.image,
    pointsRequired: redeem.pointsRequired,
  };
}

export const MOCK_ACTIVE_REDEEM: ActiveRedeemItem = buildActiveRedeem();
