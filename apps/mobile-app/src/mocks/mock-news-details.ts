import { MOCK_NEWS_LIST } from '@/mocks/mock-news';
import type { NewsArticleDetail } from '@/types/news';

export type { NewsArticleDetail };

const MOCK_NEWS_IMAGE = require('@/assets/mockImage/mock-image-news.webp');

const MOCK_NEWS_IMAGES = [MOCK_NEWS_IMAGE, MOCK_NEWS_IMAGE, MOCK_NEWS_IMAGE];

const NEWS_DETAIL_BY_SLUG: Record<
  string,
  Pick<NewsArticleDetail, 'categoryName' | 'description' | 'images'>
> = {
  'harga-emas-stabil-di-awal-tahun': {
    categoryName: 'Investasi',
    description:
      'Analis pasar komoditas mencatat stabilitas harga emas di kuartal pertama 2026. Faktor inflasi global dan permintaan safe-haven menjadi pendorong utama, sementara investor domestik mulai melirik diversifikasi portofolio jangka menengah.',
    images: MOCK_NEWS_IMAGES,
  },
  'tips-investasi-emas-pemula': {
    categoryName: 'Tips',
    description:
      'Memulai investasi emas tidak harus langsung dengan nominal besar. Pahami jenis produk, likuiditas, serta biaya penyimpanan sebelum membeli. Pemula disarankan memulai dari gramasi kecil dan memantau tren secara berkala.',
    images: MOCK_NEWS_IMAGES,
  },
  'program-loyalitas-hk-gold-2026': {
    categoryName: 'Program',
    description:
      'HK Gold memperkenalkan benefit loyalty baru pada 2026, termasuk akses event eksklusif, multiplier poin di periode tertentu, dan katalog hadiah yang diperluas untuk member aktif.',
    images: MOCK_NEWS_IMAGES,
  },
  'tren-perhiasan-emas-modern': {
    categoryName: 'Gaya Hidup',
    description:
      'Desain minimalis dan personalisasi menjadi tren utama perhiasan emas di kalangan generasi muda. Kombinasi emas dengan batu warna soft juga semakin diminati untuk gaya kasual maupun formal.',
    images: MOCK_NEWS_IMAGES,
  },
  'cara-menyimpan-emas-di-rumah': {
    categoryName: 'Tips',
    description:
      'Penyimpanan emas di rumah membutuhkan brankas berkualitas, lingkungan kering, dan pencatatan inventaris yang rapi. Hindari kontak langsung dengan bahan kimia dan pisahkan perhiasan agar tidak tergores.',
    images: MOCK_NEWS_IMAGES,
  },
  'bedah-kebijakan-pajak-emas': {
    categoryName: 'Investasi',
    description:
      'Perubahan regulasi pajak emas berdampak pada strategi pembelian dan penjualan investor. Pahami batas transaksi, pelaporan, dan implikasi biaya agar keputusan investasi tetap optimal.',
    images: MOCK_NEWS_IMAGES,
  },
  'kolaborasi-hk-gold-dan-artis-lokal': {
    categoryName: 'Berita Toko',
    description:
      'Kolaborasi terbatas HK Gold dengan artis lokal menghadirkan koleksi perhiasan emas dengan sentuhan budaya kontemporer. Koleksi ini tersedia dalam jumlah terbatas di cabang pilihan.',
    images: MOCK_NEWS_IMAGES,
  },
  'panduan-cuci-perhiasan-emas': {
    categoryName: 'Tips',
    description:
      'Gunakan larutan sabun lembut dan air hangat untuk membersihkan perhiasan emas. Sikat dengan bulu halus, bilas, lalu keringkan dengan kain microfiber. Hindari bahan abrasif yang dapat mengikis lapisan emas.',
    images: MOCK_NEWS_IMAGES,
  },
  'emas-sebagai-aset-lindung-nilai': {
    categoryName: 'Investasi',
    description:
      'Emas tetap menjadi pilihan lindung nilai ketika volatilitas pasar meningkat. Kombinasi emas fisik dan instrumen terkait emas dapat membantu menyeimbangkan risiko portofolio jangka panjang.',
    images: MOCK_NEWS_IMAGES,
  },
  'grand-opening-cabang-baru': {
    categoryName: 'Berita Toko',
    description:
      'HK Gold meresmikan cabang baru di Surabaya dengan konsep layanan premium, ruang konsultasi koleksi, dan program perkenalan member selama bulan pembukaan.',
    images: MOCK_NEWS_IMAGES,
  },
  'interview-founder-hk-gold': {
    categoryName: 'Program',
    description:
      'Founder HK Gold membahas visi membership VIP yang mengutamakan pengalaman personal, edukasi investasi emas, dan komunitas yang saling mendukung dalam perjalanan koleksi jangka panjang.',
    images: MOCK_NEWS_IMAGES,
  },
  'checklist-beli-emas-pertama': {
    categoryName: 'Tips',
    description:
      'Sebelum membeli emas pertama kali, pastikan Anda memahami jenis produk, sertifikat keaslian, harga buyback, dan rencana penyimpanan. Checklist ini membantu pemula membuat keputusan yang lebih percaya diri.',
    images: MOCK_NEWS_IMAGES,
  },
};

export function getNewsDetailBySlug(slug: string): NewsArticleDetail | null {
  const article = MOCK_NEWS_LIST.find((item) => item.slug === slug);
  const detail = NEWS_DETAIL_BY_SLUG[slug];

  if (!article || !detail) {
    return null;
  }

  return {
    ...article,
    ...detail,
  };
}
