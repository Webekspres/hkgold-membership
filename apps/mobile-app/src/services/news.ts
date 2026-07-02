import {
  MOCK_LATEST_NEWS,
  MOCK_NEWS_LIST,
} from '@/mocks/mock-news';
import { getNewsDetailBySlug } from '@/mocks/mock-news-details';

export function getNewsList() {
  return MOCK_NEWS_LIST;
}

export function getLatestNews() {
  return MOCK_LATEST_NEWS;
}

export function getNewsBySlug(slug: string) {
  return getNewsDetailBySlug(slug);
}
