import { useQuery } from '@tanstack/react-query';

import { fetchNewsPage } from '@/services/news';

export const LATEST_NEWS_LIMIT = 5;
export const LATEST_NEWS_QUERY_KEY = ['news', 'latest', { limit: LATEST_NEWS_LIMIT }] as const;

export function useLatestNews() {
  const query = useQuery({
    queryKey: LATEST_NEWS_QUERY_KEY,
    queryFn: () => fetchNewsPage({ limit: LATEST_NEWS_LIMIT }),
    staleTime: 15 * 60_000,
    retry: 1,
  });

  return {
    articles: query.data?.items ?? [],
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refetch: query.refetch,
  };
}
