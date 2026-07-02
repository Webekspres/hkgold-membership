import {
  getRewardDetailBySku,
  MOCK_REWARD_CATALOG,
  MOCK_REWARD_CATEGORIES,
  MOCK_REWARD_LIST,
} from '@/mocks/mock-rewards';

export function getRewardList() {
  return MOCK_REWARD_LIST;
}

export function getRewardCategories() {
  return MOCK_REWARD_CATEGORIES;
}

export function getRewardCatalog() {
  return MOCK_REWARD_CATALOG;
}

export function getRewardBySku(sku: string) {
  return getRewardDetailBySku(sku);
}
