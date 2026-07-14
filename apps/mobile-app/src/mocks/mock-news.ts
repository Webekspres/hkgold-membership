import type { NewsArticle } from '@/types/news';

export type { NewsArticle };

export const MOCK_NEWS_LIST: NewsArticle[] = [
  {
    id: 'news-1',
    slug: 'harga-emas-stabil-di-awal-tahun',
    title: 'Harga Emas Stabil di Awal Tahun, Simak Proyeksi Ahli',
    publishedAt: '2026-06-24T08:00:00.000Z',
    publishedAtLabel: '2 hari lalu',
    imageUrl: null,
  },
  {
    id: 'news-2',
    slug: 'tips-investasi-emas-pemula',
    title: 'Tips Investasi Emas untuk Pemula yang Ingin Mulai Koleksi',
    publishedAt: '2026-06-21T10:00:00.000Z',
    publishedAtLabel: '5 hari lalu',
    imageUrl: null,
  },
  {
    id: 'news-3',
    slug: 'program-loyalitas-hk-gold-2026',
    title: 'Program Loyalitas HK Gold 2026: Benefit Baru untuk Member',
    publishedAt: '2026-06-19T09:00:00.000Z',
    publishedAtLabel: '1 minggu lalu',
    imageUrl: null,
  },
  {
    id: 'news-4',
    slug: 'tren-perhiasan-emas-modern',
    title: 'Tren Perhiasan Emas Modern yang Diminati Generasi Muda',
    publishedAt: '2026-06-15T11:00:00.000Z',
    publishedAtLabel: '11 hari lalu',
    imageUrl: null,
  },
  {
    id: 'news-5',
    slug: 'cara-menyimpan-emas-di-rumah',
    title: 'Cara Aman Menyimpan Emas di Rumah Tanpa Khawatir',
    publishedAt: '2026-06-10T14:00:00.000Z',
    publishedAtLabel: '16 hari lalu',
    imageUrl: null,
  },
  {
    id: 'news-6',
    slug: 'bedah-kebijakan-pajak-emas',
    title: 'Bedah Kebijakan Pajak Emas Terbaru untuk Investor',
    publishedAt: '2026-06-05T08:30:00.000Z',
    publishedAtLabel: '3 minggu lalu',
    imageUrl: null,
  },
  {
    id: 'news-7',
    slug: 'kolaborasi-hk-gold-dan-artis-lokal',
    title: 'Kolaborasi HK Gold dan Artis Lokal Hadirkan Koleksi Terbatas',
    publishedAt: '2026-05-28T16:00:00.000Z',
    publishedAtLabel: '1 bulan lalu',
    imageUrl: null,
  },
  {
    id: 'news-8',
    slug: 'panduan-cuci-perhiasan-emas',
    title: 'Panduan Cuci Perhiasan Emas Agar Tetap Berkilau',
    publishedAt: '2026-05-20T09:00:00.000Z',
    publishedAtLabel: '1 bulan lalu',
    imageUrl: null,
  },
  {
    id: 'news-9',
    slug: 'emas-sebagai-aset-lindung-nilai',
    title: 'Mengapa Emas Tetap Jadi Aset Lindung Nilai di 2026',
    publishedAt: '2026-05-12T13:00:00.000Z',
    publishedAtLabel: '1 bulan lalu',
    imageUrl: null,
  },
  {
    id: 'news-10',
    slug: 'grand-opening-cabang-baru',
    title: 'Grand Opening Cabang Baru HK Gold di Surabaya',
    publishedAt: '2026-05-02T10:00:00.000Z',
    publishedAtLabel: '2 bulan lalu',
    imageUrl: null,
  },
  {
    id: 'news-11',
    slug: 'interview-founder-hk-gold',
    title: 'Interview Founder HK Gold tentang Visi Membership VIP',
    publishedAt: '2026-04-22T15:00:00.000Z',
    publishedAtLabel: '2 bulan lalu',
    imageUrl: null,
  },
  {
    id: 'news-12',
    slug: 'checklist-beli-emas-pertama',
    title: 'Checklist Membeli Emas Pertama Kali untuk Pemula',
    publishedAt: '2026-04-10T08:00:00.000Z',
    publishedAtLabel: '2 bulan lalu',
    imageUrl: null,
  },
];

export const MOCK_LATEST_NEWS = MOCK_NEWS_LIST.slice(0, 3);
