import dayjs from 'dayjs';
import type { DateType } from 'react-native-ui-datepicker';

export type DateRange = {
  startDate: DateType;
  endDate: DateType;
};

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
