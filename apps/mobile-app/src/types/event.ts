export type EventItem = {
  id: string;
  slug: string;
  title: string;
  eventDate: string;
  image: number;
};

/** @deprecated Use EventItem */
export type UpcomingEvent = EventItem;

export type EventDetail = EventItem & {
  description: string;
  images: number[];
  locationName: string;
  address: string;
  locationUrl: string | null;
};
