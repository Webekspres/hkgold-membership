import type { EventItem, UpcomingEvent } from '@/types/event';

export type { EventItem, UpcomingEvent };

const MOCK_EVENT_IMAGE = require('@/assets/mockImage/mock-image-news.webp');

export const MOCK_EVENT_LIST: EventItem[] = [
  {
    id: 'event-1',
    slug: 'gathering-member-hk-gold-2026',
    title: 'Gathering Member HK Gold 2026',
    eventDate: '2026-07-15T10:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-2',
    slug: 'workshop-investasi-emas-pemula',
    title: 'Workshop Investasi Emas untuk Pemula',
    eventDate: '2026-08-03T13:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-3',
    slug: 'pameran-perhiasan-emas-nusantara',
    title: 'Pameran Perhiasan Emas Nusantara',
    eventDate: '2026-08-22T09:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-4',
    slug: 'seminar-tren-harga-emas-2026',
    title: 'Seminar Tren Harga Emas 2026',
    eventDate: '2026-09-05T14:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-5',
    slug: 'lomba-desain-perhiasan-member',
    title: 'Lomba Desain Perhiasan Member HK Gold',
    eventDate: '2026-09-18T10:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-6',
    slug: 'talkshow-kolektor-emas',
    title: 'Talkshow Kolektor Emas Bersama Expert',
    eventDate: '2026-09-28T16:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-7',
    slug: 'open-house-cabang-jakarta-pusat',
    title: 'Open House Cabang Jakarta Pusat',
    eventDate: '2026-10-10T09:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-8',
    slug: 'peluncuran-koleksi-ramadhan',
    title: 'Peluncuran Koleksi Spesial Ramadhan',
    eventDate: '2026-10-25T11:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-9',
    slug: 'charity-auction-emas',
    title: 'Charity Auction Emas untuk Komunitas',
    eventDate: '2026-11-08T15:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-10',
    slug: 'family-fun-day-hk-gold',
    title: 'Family Fun Day HK Gold',
    eventDate: '2026-11-20T08:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-11',
    slug: 'workshop-perawatan-perhiasan',
    title: 'Workshop Perawatan Perhiasan Emas',
    eventDate: '2026-12-02T13:30:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
  {
    id: 'event-12',
    slug: 'nobar-launching-program-2027',
    title: 'Nobar Launching Program Member 2027',
    eventDate: '2026-12-15T18:00:00.000Z',
    image: MOCK_EVENT_IMAGE,
  },
];

export const MOCK_UPCOMING_EVENTS = MOCK_EVENT_LIST.slice(0, 3);
