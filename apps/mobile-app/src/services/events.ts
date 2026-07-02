import {
  MOCK_EVENT_LIST,
  MOCK_UPCOMING_EVENTS,
} from '@/mocks/mock-events';
import { getEventDetailBySlug } from '@/mocks/mock-event-details';

export function getEventList() {
  return MOCK_EVENT_LIST;
}

export function getUpcomingEvents() {
  return MOCK_UPCOMING_EVENTS;
}

export function getEventBySlug(slug: string) {
  return getEventDetailBySlug(slug);
}
