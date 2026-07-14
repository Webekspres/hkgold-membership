import { MOCK_REWARD_LIST } from '@/mocks/mock-rewards';
import type { RedeemHistoryItem } from '@/types/redeem';

const REDEEM_DATES = [
  '2026-06-28T10:30:00.000Z',
  '2026-06-22T14:15:00.000Z',
  '2026-06-15T09:00:00.000Z',
  '2026-06-08T16:45:00.000Z',
  '2026-05-30T11:20:00.000Z',
  '2026-05-18T13:10:00.000Z',
  '2026-05-05T08:50:00.000Z',
  '2026-04-24T15:30:00.000Z',
  '2026-04-12T10:05:00.000Z',
  '2026-03-29T12:40:00.000Z',
  '2026-03-14T09:25:00.000Z',
  '2026-02-20T17:00:00.000Z',
] as const;

const BRANCH_NAMES = [
  'HK Gold Mall Kelapa Gading',
  'HK Gold Grand Indonesia',
  'HK Gold Tunjungan Plaza',
  'HK Gold Pakuwon Mall',
  'HK Gold Summarecon Mall Serpong',
  'HK Gold Central Park',
] as const;

const STATUSES: RedeemHistoryItem['status'][] = [
  'selesai',
  'selesai',
  'diproses',
  'selesai',
  'selesai',
  'ditolak',
  'selesai',
  'diproses',
  'selesai',
  'selesai',
  'selesai',
  'diproses',
];

export const MOCK_REDEEM_HISTORY: RedeemHistoryItem[] = MOCK_REWARD_LIST.slice(0, 12).map(
  (reward, index) => ({
    id: `redeem-${index + 1}`,
    sku: reward.sku,
    name: reward.name,
    categoryId: reward.categoryId,
    categoryName: reward.categoryName,
    categorySlug: reward.categorySlug,
    pointsRequired: reward.pointsRequired,
    image: reward.image,
    redeemedAt: REDEEM_DATES[index],
    branchName: BRANCH_NAMES[index % BRANCH_NAMES.length],
    status: STATUSES[index],
  })
);

export function getRedeemHistoryById(id: string) {
  return MOCK_REDEEM_HISTORY.find((item) => item.id === id) ?? null;
}
