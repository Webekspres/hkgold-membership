import type { EventItem } from '@/types/event';
import { isWithinDateRange, type DateRange } from '@/lib/date-range-filter';

export type { DateRange as EventDateRange } from '@/lib/date-range-filter';
export { formatDateRangeLabel as formatEventDateRangeLabel } from '@/lib/date-range-filter';

export function filterEventsByDateRange(events: EventItem[], range: DateRange) {
  return events.filter((event) => isWithinDateRange(event.eventDate, range));
}
