import { MOCK_EVENT_LIST } from '@/mocks/mock-events';
import type { EventDetail } from '@/types/event';

export type { EventDetail };

const MOCK_EVENT_IMAGE = require('@/assets/mockImage/mock-image-news.webp');

const MOCK_EVENT_IMAGES = [MOCK_EVENT_IMAGE, MOCK_EVENT_IMAGE, MOCK_EVENT_IMAGE];

const EVENT_DETAIL_BY_SLUG: Record<
  string,
  Pick<EventDetail, 'description' | 'images' | 'locationName' | 'address' | 'locationUrl'>
> = {
  'gathering-member-hk-gold-2026': {
    description:
      'Acara tahunan untuk seluruh member HK Gold. Nikmati sesi networking, presentasi program loyalitas terbaru, dan doorprize menarik untuk peserta aktif.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'Ballroom Grand Indonesia',
    address: 'Jl. M.H. Thamrin No.1, Menteng, Jakarta Pusat',
    locationUrl: 'https://maps.google.com/?q=Grand+Indonesia+Jakarta',
  },
  'workshop-investasi-emas-pemula': {
    description:
      'Workshop interaktif mempelajari dasar investasi emas, cara membaca tren harga, serta strategi koleksi jangka panjang untuk pemula.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'HK Gold Learning Center',
    address: 'Jl. Sudirman Kav. 52-53, Jakarta Selatan',
    locationUrl: 'https://maps.google.com/?q=Sudirman+Jakarta',
  },
  'pameran-perhiasan-emas-nusantara': {
    description:
      'Pameran koleksi perhiasan emas kurasi nusantara dengan diskusi bersama perajin lokal dan sesi fitting eksklusif untuk member.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'JCC Senayan',
    address: 'Jl. Jenderal Sudirman, Senayan, Jakarta Pusat',
    locationUrl: 'https://maps.google.com/?q=JCC+Senayan',
  },
  'seminar-tren-harga-emas-2026': {
    description:
      'Seminar membahas proyeksi harga emas global dan domestik pada 2026, serta dampaknya terhadap strategi keanggotaan HK Gold.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'HK Gold Tower Auditorium',
    address: 'Jl. Gatot Subroto No.18, Jakarta Selatan',
    locationUrl: 'https://maps.google.com/?q=Gatot+Subroto+Jakarta',
  },
  'lomba-desain-perhiasan-member': {
    description:
      'Kompetisi desain perhiasan emas terbuka untuk member aktif. Pemenang akan mendapatkan kesempatan kolaborasi produksi terbatas.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'Creative Hub HK Gold',
    address: 'Jl. Kemang Raya No.8, Jakarta Selatan',
    locationUrl: 'https://maps.google.com/?q=Kemang+Jakarta',
  },
  'talkshow-kolektor-emas': {
    description:
      'Talkshow bersama kolektor emas berpengalaman membahas tips kurasi, penyimpanan, dan evaluasi nilai koleksi dari waktu ke waktu.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'Studio HK Gold Channel',
    address: 'Jl. HR Rasuna Said Kav. C-20, Jakarta Selatan',
    locationUrl: 'https://maps.google.com/?q=Rasuna+Said+Jakarta',
  },
  'open-house-cabang-jakarta-pusat': {
    description:
      'Kunjungan terbuka ke cabang Jakarta Pusat dengan tur fasilitas, konsultasi koleksi emas, dan promo spesial member selama acara.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'HK Gold Cabang Jakarta Pusat',
    address: 'Jl. Tanah Abang No.12, Jakarta Pusat',
    locationUrl: 'https://maps.google.com/?q=Tanah+Abang+Jakarta',
  },
  'peluncuran-koleksi-ramadhan': {
    description:
      'Peluncuran koleksi perhiasan emas edisi Ramadhan dengan showcase produk terbaru dan sesi styling bersama fashion stylist.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'HK Gold Flagship Store',
    address: 'Jl. Asia Afrika No.8, Bandung',
    locationUrl: 'https://maps.google.com/?q=Asia+Afrika+Bandung',
  },
  'charity-auction-emas': {
    description:
      'Charity auction koleksi emas terkurasi dengan hasil terbatas untuk program sosial komunitas HK Gold di berbagai kota.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'Hotel Indonesia Kempinski',
    address: 'Jl. M.H. Thamrin No.13, Jakarta Pusat',
    locationUrl: 'https://maps.google.com/?q=Hotel+Indonesia+Kempinski',
  },
  'family-fun-day-hk-gold': {
    description:
      'Acara keluarga dengan games, workshop mini untuk anak, dan booth edukasi emas yang ramah untuk semua usia.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'Taman Literasi HK Gold',
    address: 'Jl. Raya Bogor KM 29, Depok',
    locationUrl: 'https://maps.google.com/?q=Depok',
  },
  'workshop-perawatan-perhiasan': {
    description:
      'Pelatihan praktis merawat perhiasan emas di rumah, termasuk teknik pembersihan aman dan tips penyimpanan jangka panjang.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'HK Gold Workshop Studio',
    address: 'Jl. Gajah Mada No.88, Jakarta Barat',
    locationUrl: 'https://maps.google.com/?q=Gajah+Mada+Jakarta',
  },
  'nobar-launching-program-2027': {
    description:
      'Nonton bareng peluncuran program member 2027 dengan sesi tanya jawab langsung bersama tim HK Gold dan giveaway eksklusif.',
    images: MOCK_EVENT_IMAGES,
    locationName: 'HK Gold Member Lounge',
    address: 'Jl. Panglima Polim No.5, Jakarta Selatan',
    locationUrl: 'https://maps.google.com/?q=Panglima+Polim+Jakarta',
  },
};

export function getEventDetailBySlug(slug: string): EventDetail | null {
  const event = MOCK_EVENT_LIST.find((item) => item.slug === slug);
  const detail = EVENT_DETAIL_BY_SLUG[slug];

  if (!event || !detail) {
    return null;
  }

  return {
    ...event,
    ...detail,
  };
}
