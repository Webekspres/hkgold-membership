import type { RewardCatalogItem } from '@/constants/mock-rewards';

export type RewardFilterState = {
  categoryIds: number[];
  pointsMin: number;
  pointsMax: number;
};

export type RewardPointsBounds = {
  min: number;
  max: number;
};

export function getRewardPointsBounds(rewards: RewardCatalogItem[]): RewardPointsBounds {
  if (rewards.length === 0) {
    return { min: 0, max: 0 };
  }

  const points = rewards.map((reward) => reward.pointsRequired);

  return {
    min: Math.min(...points),
    max: Math.max(...points),
  };
}

export function createDefaultRewardFilter(bounds: RewardPointsBounds): RewardFilterState {
  return {
    categoryIds: [],
    pointsMin: bounds.min,
    pointsMax: bounds.max,
  };
}

export function filterRewardsByCategories(
  rewards: RewardCatalogItem[],
  categoryIds: number[]
) {
  if (categoryIds.length === 0) {
    return rewards;
  }

  return rewards.filter((reward) => categoryIds.includes(reward.categoryId));
}

export function filterRewardsByPointsRange(
  rewards: RewardCatalogItem[],
  pointsMin: number,
  pointsMax: number
) {
  return rewards.filter(
    (reward) => reward.pointsRequired >= pointsMin && reward.pointsRequired <= pointsMax
  );
}

export function applyRewardFilters(
  rewards: RewardCatalogItem[],
  filter: RewardFilterState
) {
  const byCategory = filterRewardsByCategories(rewards, filter.categoryIds);
  return filterRewardsByPointsRange(byCategory, filter.pointsMin, filter.pointsMax);
}

export function hasActiveRewardFilter(
  filter: RewardFilterState,
  bounds: RewardPointsBounds
) {
  const hasCategoryFilter = filter.categoryIds.length > 0;
  const hasPointsFilter =
    filter.pointsMin > bounds.min || filter.pointsMax < bounds.max;

  return hasCategoryFilter || hasPointsFilter;
}
