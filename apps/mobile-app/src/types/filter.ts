import type { DateType } from 'react-native-ui-datepicker';

export type DateRange = {
  startDate: DateType;
  endDate: DateType;
};

export type RewardFilterState = {
  categoryIds: number[];
  pointsMin: number;
  pointsMax: number;
};

export type RewardPointsBounds = {
  min: number;
  max: number;
};
