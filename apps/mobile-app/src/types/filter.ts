import type { DateType } from 'react-native-ui-datepicker';

export type DateRange = {
  startDate: DateType;
  endDate: DateType;
};

export type RewardSortBy = 'sku' | 'name' | 'points';
export type RewardSortOrder = 'asc' | 'desc';

export type RewardFilterState = {
  categoryIds: number[];
  pointsMin: number;
  pointsMax: number;
  sortBy: RewardSortBy;
  sortOrder: RewardSortOrder;
};

export type RewardPointsBounds = {
  min: number;
  max: number;
};
