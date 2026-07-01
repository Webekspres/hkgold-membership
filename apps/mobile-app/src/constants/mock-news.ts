export type NewsArticle = {
  id: string;
  slug: string;
  title: string;
  publishedAtLabel: string;
  image: number;
};

export const MOCK_LATEST_NEWS: NewsArticle[] = [
  {
    id: 'news-1',
    slug: 'harga-emas-stabil-di-awal-tahun',
    title: 'Harga Emas Stabil di Awal Tahun, Simak Proyeksi Ahli',
    publishedAtLabel: '2 hari lalu',
    image: require('@/assets/mockImage/mock-image-news.webp'),
  },
  {
    id: 'news-2',
    slug: 'tips-investasi-emas-pemula',
    title: 'Tips Investasi Emas untuk Pemula yang Ingin Mulai Koleksi',
    publishedAtLabel: '5 hari lalu',
    image: require('@/assets/mockImage/mock-image-news.webp'),
  },
  {
    id: 'news-3',
    slug: 'program-loyalitas-hk-gold-2026',
    title: 'Program Loyalitas HK Gold 2026: Benefit Baru untuk Member',
    publishedAtLabel: '1 minggu lalu',
    image: require('@/assets/mockImage/mock-image-news.webp'),
  },
];
