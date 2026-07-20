import { describe, expect, test } from 'bun:test';

import { mapActiveRedeemToReward } from '@/lib/active-redeem/map-active-redeem-reward';
import type { ActiveRedeemItem } from '@/types/active-redeem';

describe('mapActiveRedeemToReward', () => {
  test('maps active redeem fields to reward catalog shape', () => {
    const active: ActiveRedeemItem = {
      redeemId: 'token-1',
      tokenCode: 'XYZ',
      heldPoints: 2500,
      isUsed: false,
      expiresAt: '2026-12-31T00:00:00.000Z',
      reward: {
        id: 'reward-99',
        sku: 'GOLD-99',
        name: 'Emas 5g',
        imageUrl: 'https://cdn.example/5g.png',
      },
      branch: {
        id: 3,
        name: 'Cabang B',
        address: 'Jl. B 2',
      },
    };

    expect(mapActiveRedeemToReward(active)).toEqual({
      id: 'reward-99',
      sku: 'GOLD-99',
      name: 'Emas 5g',
      categoryId: 0,
      categoryName: 'Cabang B',
      categorySlug: '',
      pointsRequired: 2500,
      stockRemaining: 0,
      image: 'https://cdn.example/5g.png',
    });
  });
});
