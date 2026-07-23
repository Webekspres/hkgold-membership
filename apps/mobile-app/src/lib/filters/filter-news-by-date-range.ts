import type { NewsArticle } from '@/types/news';
import { isWithinDateRange, type DateRange } from '@/lib/date-range-filter';

export function filterNewsByDateRange(articles: NewsArticle[], range: DateRange) {
  return articles.filter((article) => isWithinDateRange(article.publishedAt, range));
}
