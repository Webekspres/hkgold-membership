import { MOCK_EVENT_LIST } from '@/mocks/mock-events';
import type { EventDetail } from '@/types/event';

export type { EventDetail };

/** Legacy mock — list/detail event sudah pakai API. */
export function getEventDetailBySlug(slug: string): EventDetail | null {
  const event = MOCK_EVENT_LIST.find((item) => item.slug === slug);
  if (!event) return null;

  return {
    ...event,
    bodyContent: 'Konten mock.',
    imageUrls: [],
  };
}
