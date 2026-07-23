import { MOCK_NEWS_LIST } from '@/mocks/mock-news';
import type { NewsArticleDetail } from '@/types/news';

export type { NewsArticleDetail };

/** Legacy mock — list/detail berita sudah pakai API. */
export function getNewsDetailBySlug(slug: string): NewsArticleDetail | null {
  const article = MOCK_NEWS_LIST.find((item) => item.slug === slug);
  if (!article) return null;

  return {
    ...article,
    bodyContent: 'Konten mock.',
    imageUrls: [],
  };
}
