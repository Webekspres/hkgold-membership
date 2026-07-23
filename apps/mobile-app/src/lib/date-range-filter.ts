import dayjs from 'dayjs';

import type { DateRange } from '@/types/filter';

export type { DateRange };

export const EMPTY_DATE_RANGE: DateRange = {
  startDate: undefined,
  endDate: undefined,
};

export function isWithinDateRange(isoDate: string, range: DateRange) {
  const { startDate, endDate } = range;

  if (!startDate && !endDate) {
    return true;
  }

  const date = dayjs(isoDate);

  if (startDate && date.isBefore(dayjs(startDate).startOf('day'))) {
    return false;
  }

  if (endDate && date.isAfter(dayjs(endDate).endOf('day'))) {
    return false;
  }

  return true;
}

export function formatDateRangeLabel(range: DateRange) {
  const { startDate, endDate } = range;

  if (!startDate && !endDate) {
    return 'Semua tanggal';
  }

  if (startDate && endDate) {
    return `${dayjs(startDate).format('D MMM YYYY')} – ${dayjs(endDate).format('D MMM YYYY')}`;
  }

  if (startDate) {
    return `Dari ${dayjs(startDate).format('D MMM YYYY')}`;
  }

  return `Sampai ${dayjs(endDate).format('D MMM YYYY')}`;
}

export function hasActiveDateRange(range: DateRange) {
  return Boolean(range.startDate || range.endDate);
}

/** ISO date (YYYY-MM-DD) untuk query API content. */
export function dateRangeToApiParams(range: DateRange): {
  dateFrom?: string;
  dateTo?: string;
} {
  return {
    ...(range.startDate
      ? { dateFrom: dayjs(range.startDate).format('YYYY-MM-DD') }
      : {}),
    ...(range.endDate ? { dateTo: dayjs(range.endDate).format('YYYY-MM-DD') } : {}),
  };
}
