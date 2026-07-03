import { getRedeemHistoryById, MOCK_REDEEM_HISTORY } from '@/mocks/mock-redeem-history';

export function getRedeemHistoryList() {
  return MOCK_REDEEM_HISTORY;
}

export function getRedeemHistoryItemById(id: string) {
  return getRedeemHistoryById(id);
}
