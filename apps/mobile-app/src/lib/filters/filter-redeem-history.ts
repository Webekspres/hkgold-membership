import { EMPTY_DATE_RANGE, isWithinDateRange } from '@/lib/date-range-filter';
import {
  createDefaultRewardFilter,
  hasActiveRewardFilter,
} from '@/lib/filters/filter-rewards';
import type { DateRange, RewardFilterState, RewardPointsBounds } from '@/types/filter';
import type { RedeemHistoryItem } from '@/types/redeem';

export type RedeemHistoryFilterState = RewardFilterState & {
  dateRange: DateRange;
};

export function createDefaultRedeemHistoryFilter(
  bounds: RewardPointsBounds
): RedeemHistoryFilterState {
  return {
    ...createDefaultRewardFilter(bounds),
    dateRange: EMPTY_DATE_RANGE,
  };
}

export function getRedeemHistoryPointsBounds(items: RedeemHistoryItem[]): RewardPointsBounds {
  if (items.length === 0) {
    return { min: 0, max: 0 };
  }

  const points = items.map((item) => item.pointsRedeemed);

  return {
    min: Math.min(...points),
    max: Math.max(...points),
  };
}

export function applyRedeemHistoryFilters(
  items: RedeemHistoryItem[],
  filter: RedeemHistoryFilterState
): RedeemHistoryItem[] {
  // ponytail: API history belum expose categoryId — filter kategori diabaikan sampai kontrak ditambah
  const byPoints = items.filter(
    (item) =>
      item.pointsRedeemed >= filter.pointsMin && item.pointsRedeemed <= filter.pointsMax
  );

  return byPoints.filter((item) => isWithinDateRange(item.redeemedAt, filter.dateRange));
}

export function hasActiveRedeemHistoryFilter(
  filter: RedeemHistoryFilterState,
  bounds: RewardPointsBounds
) {
  const hasDateFilter = Boolean(filter.dateRange.startDate || filter.dateRange.endDate);
  return hasActiveRewardFilter(filter, bounds) || hasDateFilter;
}
