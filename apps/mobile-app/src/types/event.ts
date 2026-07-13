export type EventItem = {
  id: string;
  slug: string;
  title: string;
  eventDate: string;
  imageUrl: string | null;
};

/** @deprecated Use EventItem */
export type UpcomingEvent = EventItem;

export type EventDetail = EventItem & {
  bodyContent: string;
  imageUrls: string[];
};

export type EventPage = {
  items: EventItem[];
  nextCursor: string | null;
  hasMore: boolean;
};
