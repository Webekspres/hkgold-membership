import { describe, expect, test } from 'bun:test';

import { resolveProfileRedeemHighlight } from './resolve-profile-redeem-highlight';
import type { ActiveRedeemItem } from '@/types/active-redeem';
import type { RedeemHistoryItem } from '@/types/redeem';

const activeRedeem: ActiveRedeemItem = {
  redeemId: 'token-1',
  tokenCode: 'ABC123',
  heldPoints: 1000,
  isUsed: false,
  expiresAt: '2026-12-31T00:00:00.000Z',
  reward: {
    id: 'reward-1',
    sku: 'SKU-1',
    name: 'Emas 1g',
    imageUrl: 'https://cdn.example/gold.png',
  },
  branch: {
    id: 1,
    name: 'Cabang A',
    address: 'Jl. A 1',
  },
};

const completedHistory: RedeemHistoryItem = {
  id: 'inv-1',
  invoiceNumber: 'INV-001',
  pointsRedeemed: 1000,
  redeemedAt: '2026-07-01T00:00:00.000Z',
  status: 'selesai',
  reward: {
    id: 'reward-1',
    sku: 'SKU-1',
    name: 'Emas 1g',
    imageUrl: null,
  },
  branch: {
    id: 1,
    name: 'Cabang A',
    address: 'Jl. A 1',
  },
};

describe('resolveProfileRedeemHighlight', () => {
  test('active redeem → pending highlight', () => {
    const result = resolveProfileRedeemHighlight({
      activeRedeem,
      latestHistoryItem: completedHistory,
    });

    expect(result.kind).toBe('pending');
    if (result.kind === 'pending') {
      expect(result.title).toBe('Hadiah sedang diklaim');
      expect(result.href).toBe('/card/redeem-qr');
      expect(result.reward.name).toBe('Emas 1g');
      expect(result.activeRedeem.redeemId).toBe('token-1');
    }
  });

  test('no active + latest selesai → completed highlight', () => {
    const result = resolveProfileRedeemHighlight({
      activeRedeem: null,
      latestHistoryItem: completedHistory,
    });

    expect(result.kind).toBe('completed');
    if (result.kind === 'completed') {
      expect(result.invoiceId).toBe('inv-1');
      expect(result.href).toEqual({ pathname: '/redeem/[id]', params: { id: 'inv-1' } });
      expect(result.reward.pointsRequired).toBe(1000);
    }
  });

  test('latest REFUNDED (ditolak) → empty — current behavior', () => {
    const result = resolveProfileRedeemHighlight({
      activeRedeem: null,
      latestHistoryItem: { ...completedHistory, status: 'ditolak' },
    });

    expect(result).toEqual({ kind: 'empty' });
  });

  test('no active and no history → empty', () => {
    expect(
      resolveProfileRedeemHighlight({
        activeRedeem: null,
        latestHistoryItem: undefined,
      }),
    ).toEqual({ kind: 'empty' });
  });
});
